<?php

namespace App\Services\NewsAggregator;

use App\Models\Article;
use Illuminate\Support\Facades\Log;
use App\Services\NewsAggregator\Contracts\NewsServiceInterface;
use App\Services\NewsAggregator\Adapters\NewsApiAdapter;
use App\Services\NewsAggregator\Adapters\GuardianAdapter;
use App\Services\NewsAggregator\Adapters\NYTimesAdapter;

class NewsAggregatorService
{
    private array $services = [];

    public function __construct()
    {
        $this->initializeServices();
    }

    /**
     * Initialize all news services.
     */
    private function initializeServices(): void
    {
        // NewsAPI
        if (config('services.newsapi.key')) {
            $this->services['newsapi'] = new NewsApiAdapter(
                config('services.newsapi.url'),
                config('services.newsapi.key')
            );
        }

        // Guardian API
        if (config('services.guardian.key')) {
            $this->services['guardian'] = new GuardianAdapter(
                config('services.guardian.url'),
                config('services.guardian.key')
            );
        }

        // New York Times API
        if (config('services.nytimes.key')) {
            $this->services['nytimes'] = new NYTimesAdapter(
                config('services.nytimes.url'),
                config('services.nytimes.key')
            );
        }
    }

    /**
     * Aggregate articles from all configured services.
     *
     * @param array $params
     * @return array
     */
    public function aggregateArticles(array $params = []): array
    {
        $allArticles = [];
        $stats = [
            'total_fetched' => 0,
            'total_saved' => 0,
            'duplicates_skipped' => 0,
            'errors' => 0,
        ];

        foreach ($this->services as $serviceName => $service) {
            try {
                Log::info("Fetching articles from {$service->getSourceName()}");

                $articleDtos = $service->fetchArticles($params);
                $stats['total_fetched'] += count($articleDtos);

                foreach ($articleDtos as $articleDto) {
                    try {
                        // Validate DTO before processing
                        if (!$articleDto->isValid()) {
                            Log::warning("Invalid article from {$service->getSourceName()}", [
                                'url' => $articleDto->url
                            ]);
                            continue;
                        }

                        // Check for duplicates
                        $existingArticle = Article::where('url', $articleDto->url)->first();
                        if ($existingArticle) {
                            $stats['duplicates_skipped']++;
                            continue;
                        }

                        // Save the article using DTO
                        Article::create($articleDto->toArray());
                        $stats['total_saved']++;

                        $allArticles[] = $articleDto->toArray();

                    } catch (\Exception $e) {
                        Log::error("Error processing article from {$service->getSourceName()}", [
                            'error' => $e->getMessage(),
                            'url' => $articleDto->url ?? 'unknown'
                        ]);
                        $stats['errors']++;
                    }
                }

                Log::info("Completed fetching from {$service->getSourceName()}", [
                    'articles_processed' => count($articleDtos)
                ]);

            } catch (\Exception $e) {
                Log::error("Error fetching from {$service->getSourceName()}", [
                    'error' => $e->getMessage()
                ]);
                $stats['errors']++;
            }
        }

        Log::info('News aggregation completed', $stats);

        return [
            'articles' => $allArticles,
            'stats' => $stats
        ];
    }

    /**
     * Get all available services.
     *
     * @return array
     */
    public function getAvailableServices(): array
    {
        return array_keys($this->services);
    }

    /**
     * Get a specific service.
     *
     * @param string $serviceName
     * @return NewsServiceInterface|null
     */
    public function getService(string $serviceName): ?NewsServiceInterface
    {
        return $this->services[$serviceName] ?? null;
    }

    /**
     * Clean up old articles.
     *
     * @param int $daysToKeep
     * @return int Number of articles deleted
     */
    public function cleanupOldArticles(int $daysToKeep = 30): int
    {
        $cutoffDate = now()->subDays($daysToKeep);

        return Article::where('published_at', '<', $cutoffDate)->delete();
    }
}

