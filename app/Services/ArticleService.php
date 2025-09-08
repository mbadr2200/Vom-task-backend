<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use App\Services\NewsAggregator\CategoryMappingService;

class ArticleService
{
    public function getSources(): Collection
    {
        return Cache::remember('article_sources', 3600, function () {
            return Article::availableSources()->pluck('source_name');
        });
    }

    public function getStandardCategories(): array
    {
        return CategoryMappingService::getStandardCategories();
    }

    public function getAvailableCategories(): Collection
    {
        return Cache::remember('article_categories', 3600, function () {
            return Article::availableCategories()->pluck('category');
        });
    }
}
