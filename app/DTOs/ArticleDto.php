<?php

namespace App\DTOs;

use Carbon\Carbon;

class ArticleDto
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $content,
        public readonly ?string $description,
        public readonly string $url,
        public readonly ?string $imageUrl,
        public readonly string $sourceName,
        public readonly ?string $sourceId,
        public readonly ?string $category,
        public readonly Carbon $publishedAt
    ) {}

    /**
     * Create DTO from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? 'No Title',
            content: $data['content'] ?? null,
            description: $data['description'] ?? null,
            url: $data['url'] ?? '',
            imageUrl: $data['image_url'] ?? null,
            sourceName: $data['source_name'] ?? '',
            sourceId: $data['source_id'] ?? null,
            category: $data['category'] ?? null,
            publishedAt: $data['published_at'] ?? now()
        );
    }

    /**
     * Convert DTO to array for database storage.
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'description' => $this->description,
            'url' => $this->url,
            'image_url' => $this->imageUrl,
            'source_name' => $this->sourceName,
            'source_id' => $this->sourceId,
            'category' => $this->category,
            'published_at' => $this->publishedAt,
        ];
    }

    /**
     * Validate the DTO data.
     */
    public function isValid(): bool
    {
        return !empty($this->title) &&
               !empty($this->url) &&
               filter_var($this->url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Get a summary of the article.
     */
    public function getSummary(int $length = 150): string
    {
        $text = $this->description ?? $this->content ?? '';
        return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
    }
}
