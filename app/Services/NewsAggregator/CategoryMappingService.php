<?php

namespace App\Services\NewsAggregator;

class CategoryMappingService
{
    /**
     * Standard categories used across the application.
     * This could be extracted to a database table with relation
     * but for the sake of simplicity (and time obviously 😁 ) i prefer to keep it as data
     * inside the articles table
     */
    public const STANDARD_CATEGORIES = [
        'business',
        'entertainment',
        'general',
        'health',
        'science',
        'sports',
        'technology'
    ];

    /**
     * Guardian section to category mapping.
     */
    public const GUARDIAN_MAPPING = [
        'business' => 'business',
        'technology' => 'technology',
        'science' => 'science',
        'sport' => 'sports',
        'football' => 'sports',
        'culture' => 'entertainment',
        'film' => 'entertainment',
        'music' => 'entertainment',
        'books' => 'entertainment',
        'artanddesign' => 'entertainment',
        'stage' => 'entertainment',
        'tv-and-radio' => 'entertainment',
        'games' => 'entertainment',
        'lifeandstyle' => 'general',
        'fashion' => 'general',
        'food' => 'general',
        'travel' => 'general',
        'money' => 'business',
        'politics' => 'general',
        'world' => 'general',
        'uk-news' => 'general',
        'us-news' => 'general',
        'australia-news' => 'general',
        'society' => 'general',
        'education' => 'general',
        'media' => 'general',
        'law' => 'general',
        'environment' => 'science',
        'global-development' => 'general',
        'cities' => 'general',
        'commentisfree' => 'general',
        'opinion' => 'general',
    ];

    /**
     * NYT section to category mapping.
     */
    public const NYT_MAPPING = [
        'business' => 'business',
        'technology' => 'technology',
        'science' => 'science',
        'sports' => 'sports',
        'arts' => 'entertainment',
        'movies' => 'entertainment',
        'music' => 'entertainment',
        'books' => 'entertainment',
        'theater' => 'entertainment',
        'style' => 'general',
        'fashion' => 'general',
        'food' => 'general',
        'travel' => 'general',
        'politics' => 'general',
        'world' => 'general',
        'us' => 'general',
        'nyregion' => 'general',
        'opinion' => 'general',
        'health' => 'health',
        'well' => 'health',
        'education' => 'general',
        'magazine' => 'general',
        'sunday-review' => 'general',
        't-magazine' => 'entertainment',
        'real estate' => 'business',
        'automobiles' => 'general',
        'obituaries' => 'general',
        'climate' => 'science',
        'environment' => 'science',
    ];

    /**
     * Map Guardian section to standard category.
     */
    public static function mapGuardianSection(?string $section): ?string
    {
        if (!$section) {
            return null;
        }

        $sectionKey = strtolower(str_replace(' ', '', $section));
        return self::GUARDIAN_MAPPING[$sectionKey] ?? 'general';
    }

    /**
     * Map NYT section to standard category.
     */
    public static function mapNYTSection(?string $section): ?string
    {
        if (!$section) {
            return null;
        }

        $sectionKey = strtolower(str_replace([' ', '-'], '', $section));
        return self::NYT_MAPPING[$sectionKey] ?? 'general';
    }

    /**
     * Get all standard categories.
     */
    public static function getStandardCategories(): array
    {
        return self::STANDARD_CATEGORIES;
    }

    /**
     * Check if a category is valid.
     */
    public static function isValidCategory(string $category): bool
    {
        return in_array($category, self::STANDARD_CATEGORIES);
    }
}
