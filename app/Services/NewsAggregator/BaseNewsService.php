<?php

namespace App\Services\NewsAggregator;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\NewsAggregator\Contracts\NewsServiceInterface;
abstract class BaseNewsService implements NewsServiceInterface
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
    }

    /**
     * Make HTTP request to the news API.
     *
     * @param string $endpoint
     * @param array $params
     * @return array
     */
    protected function makeRequest(string $endpoint, array $params = []): array
    {
        try {
            $response = Http::timeout(30)->get($this->baseUrl . $endpoint, $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("News API request failed for {$this->getSourceName()}", [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error("News API request exception for {$this->getSourceName()}", [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Parse date string to Carbon instance.
     *
     * @param string|null $dateString
     * @return Carbon|null
     */
    protected function parseDate(?string $dateString): ?Carbon
    {
        if (!$dateString) {
            return null;
        }

        try {
            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            Log::warning("Failed to parse date: {$dateString}");
            return null;
        }
    }

    /**
     * Clean and truncate text content.
     *
     * @param string|null $content
     * @param int $maxLength
     * @return string|null
     */
    protected function cleanContent(?string $content, int $maxLength = 10000): ?string
    {
        if (!$content) {
            return null;
        }

        $content = html_entity_decode(strip_tags($content));

        if (strlen($content) > $maxLength) {
            $content = substr($content, 0, $maxLength) . '...';
        }

        return trim($content);
    }
}

