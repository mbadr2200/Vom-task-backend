<?php

namespace App\DTOs;

use Carbon\Carbon;
use App\Services\NewsAggregator\CategoryMappingService;

class NYTimesArticleDto extends ArticleDto
{
    public static function fromNYTimesResponse(array $article): self
    {
        $category = CategoryMappingService::mapNYTSection($article['section_name'] ?? null);

        // Get the main image if available
        $imageUrl = null;
        if (!empty($article['multimedia']) && is_array($article['multimedia'])) {
            foreach ($article['multimedia'] as $media) {
                if (is_array($media) &&
                    isset($media['type']) && $media['type'] === 'image' &&
                    !empty($media['url'])) {
                    $imageUrl = 'https://www.nytimes.com/' . $media['url'];
                    break;
                }
            }
        }

        return new self(
            title: $article['headline']['main'] ?? 'No Title',
            content: $article['lead_paragraph'] ?? $article['snippet'] ?? null,
            description: $article['abstract'] ?? $article['snippet'] ?? null,
            url: $article['web_url'] ?? '',
            imageUrl: $imageUrl,
            sourceName: 'New York Times',
            sourceId: $article['_id'] ?? null,
            category: $category,
            publishedAt: Carbon::parse($article['pub_date'] ?? now())
        );
    }
}
