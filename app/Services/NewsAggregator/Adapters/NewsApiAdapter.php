<?php

namespace App\Services\NewsAggregator\Adapters;

use App\DTOs\NewsApiArticleDto;
use App\Services\NewsAggregator\BaseNewsService;

class NewsApiAdapter extends BaseNewsService
{
    public function fetchArticles(array $params = []): array
    {
        $defaultParams = [
            'apiKey' => $this->apiKey,
            'language' => 'en',
            'sortBy' => 'publishedAt',
            'pageSize' => 100,
        ];

        $queryParams = array_merge($defaultParams, $params);
        $data = $this->makeRequest('/everything', $queryParams);

        return array_map(
            fn($article) => NewsApiArticleDto::fromNewsApiResponse($article),
            $data['articles'] ?? []
        );
    }

    public function normalizeArticle(array $article): array
    {
        $dto = NewsApiArticleDto::fromNewsApiResponse($article);
        return $dto->toArray();
    }

    public function getSourceName(): string
    {
        return 'NewsAPI';
    }
}

