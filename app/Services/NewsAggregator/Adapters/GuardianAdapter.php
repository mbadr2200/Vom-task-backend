<?php

namespace App\Services\NewsAggregator\Adapters;

use Carbon\Carbon;
use App\Services\NewsAggregator\BaseNewsService;
use App\Services\NewsAggregator\CategoryMappingService;
use App\DTOs\GuardianArticleDto;

class GuardianAdapter extends BaseNewsService
{
    public function fetchArticles(array $params = []): array
    {
        $defaultParams = [
            'api-key' => $this->apiKey,
            'show-fields' => 'headline,byline,body,thumbnail',
            'show-tags' => 'keyword',
            'page-size' => 50,
            'order-by' => 'newest',
        ];

        $queryParams = array_merge($defaultParams, $params);

        $data = $this->makeRequest('/search', $queryParams);

        return array_map(
            fn($article) => GuardianArticleDto::fromGuardianResponse($article),
            $data['response']['results'] ?? []
        );
    }

    public function normalizeArticle(array $article): array
    {
        $dto = GuardianArticleDto::fromGuardianResponse($article);
        return $dto->toArray();
    }

    public function getSourceName(): string
    {
        return 'The Guardian';
    }
}

