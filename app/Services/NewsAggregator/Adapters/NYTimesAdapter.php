<?php

namespace App\Services\NewsAggregator\Adapters;

use App\DTOs\NYTimesArticleDto;
use App\Services\NewsAggregator\BaseNewsService;

class NYTimesAdapter extends BaseNewsService
{
    public function fetchArticles(array $params = []): array
    {
        $defaultParams = [
            'api-key' => $this->apiKey,
        ];

        $queryParams = array_merge($defaultParams, $params);

        $data = $this->makeRequest('/search/v2/articlesearch.json', $queryParams);

        return array_map(
            fn($article) => NYTimesArticleDto::fromNYTimesResponse($article),
            $data['response']['docs'] ?? []
        );
    }

    public function normalizeArticle(array $article): array
    {
        $dto = NYTimesArticleDto::fromNYTimesResponse($article);
        return $dto->toArray();
    }

    public function getSourceName(): string
    {
        return 'New York Times';
    }
}

