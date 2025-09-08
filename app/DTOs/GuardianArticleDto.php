<?php

namespace App\DTOs;

use Carbon\Carbon;
use App\Services\NewsAggregator\CategoryMappingService;

class GuardianArticleDto extends ArticleDto
{
    public static function fromGuardianResponse(array $article): self
    {
        $category = CategoryMappingService::mapGuardianSection($article['sectionName'] ?? null);

        return new self(
            title: $article['fields']['headline'] ?? $article['webTitle'] ?? 'No Title',
            content: $article['fields']['body'] ?? null,
            description: $article['fields']['trailText'] ?? null,
            url: $article['webUrl'] ?? '',
            imageUrl: $article['fields']['thumbnail'] ?? null,
            sourceName: 'The Guardian',
            sourceId: $article['id'] ?? null,
            category: $category,
            publishedAt: Carbon::parse($article['webPublicationDate'] ?? now())
        );
    }
}
