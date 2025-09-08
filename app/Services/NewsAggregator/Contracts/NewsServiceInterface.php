<?php

namespace App\Services\NewsAggregator\Contracts;

interface NewsServiceInterface
{
    /**
     * Fetch articles from the news source.
     *
     * @param array $params
     * @return array
     */
    public function fetchArticles(array $params = []): array;

    /**
     * Normalize article data to standard format.
     *
     * @param array $article
     * @return array
     */
    public function normalizeArticle(array $article): array;

    /**
     * Get the source name.
     *
     * @return string
     */
    public function getSourceName(): string;
}

