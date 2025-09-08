<?php

namespace App\DTOs;

use Carbon\Carbon;

class NewsApiArticleDto extends ArticleDto
{
    public static function fromNewsApiResponse(array $article): self
    {
        return new self(
            title: $article['title'] ?? 'No Title',
            content: $article['content'] ?? null,
            description: $article['description'] ?? null,
            url: $article['url'] ?? '',
            imageUrl: $article['urlToImage'] ?? null,
            sourceName: 'NewsAPI',
            sourceId: $article['source']['id'] ?? null,
            category: null, // NewsAPI doesn't provide categories
            publishedAt: Carbon::parse($article['publishedAt'] ?? now())
        );
    }
}
