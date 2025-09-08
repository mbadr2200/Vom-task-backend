<?php

namespace App\Console\Commands;

use App\Services\NewsAggregator\NewsAggregatorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchNewsArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:fetch
                            {--source= : Fetch from specific source (newsapi, guardian, nytimes)}
                            {--days= : Number of days to fetch (default: 1)}
                            {--cleanup : Clean up old articles after fetching}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news articles from configured sources and store them in the database';

    /**
     * Execute the console command.
     */
    public function handle(NewsAggregatorService $aggregatorService)
    {
        $this->info('🚀 Starting news aggregation...');

        $startTime = microtime(true);

        try {
            // Prepare parameters for fetching
            $params = $this->buildFetchParameters();

            // Show what we're fetching
            $this->displayFetchInfo($params);

            // Fetch articles
            $result = $aggregatorService->aggregateArticles($params);

            // Display results
            $this->displayResults($result['stats']);

            // Cleanup old articles if requested
            if ($this->option('cleanup')) {
                $this->info('🧹 Cleaning up old articles...');
                $deletedCount = $aggregatorService->cleanupOldArticles(30);
                $this->info("   Deleted {$deletedCount} old articles");
            }

            $executionTime = round(microtime(true) - $startTime, 2);
            $this->info("✅ News aggregation completed in {$executionTime}s");

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error during news aggregation: ' . $e->getMessage());
            Log::error('News aggregation command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return self::FAILURE;
        }
    }

    /**
     * Build parameters for fetching articles.
     *
     * @return array
     */
    private function buildFetchParameters(): array
    {
        $params = [];

        // Add date range (fetch from X days ago to now)
        $days = (int) $this->option('days') ?: 1;
        $params['from'] = now()->subDays($days)->toISOString();
        $params['to'] = now()->toISOString();


        // Add source-specific parameters
        $source = $this->option('source');
        if ($source) {
            switch ($source) {
                case 'newsapi':
                    $params['sortBy'] = 'publishedAt';
                    $params['language'] = 'en';
                    break;
                case 'guardian':
                    $params['order-by'] = 'newest';
                    break;
                case 'nytimes':
                    $params['sort'] = 'newest';
                    break;
            }
        }

        return $params;
    }

    /**
     * Display information about what we're fetching.
     *
     * @param array $params
     */
    private function displayFetchInfo(array $params): void
    {
        $this->info('📰 Fetch Configuration:');

        if ($this->option('source')) {
            $this->line("   Source: " . $this->option('source'));
        } else {
            $this->line("   Sources: All configured sources");
        }

        $days = $this->option('days') ?: 1;
        $this->line("   Time Range: Last {$days} day(s)");

        $this->newLine();
    }

    /**
     * Display the results of the aggregation.
     *
     * @param array $stats
     */
    private function displayResults(array $stats): void
    {
        $this->info('📊 Aggregation Results:');
        $this->line("   Articles Fetched: {$stats['total_fetched']}");
        $this->line("   Articles Saved: {$stats['total_saved']}");
        $this->line("   Duplicates Skipped: {$stats['duplicates_skipped']}");

        if ($stats['errors'] > 0) {
            $this->warn("   Errors Encountered: {$stats['errors']}");
        }

        $this->newLine();
    }
}
