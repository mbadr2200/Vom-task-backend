<?php

use App\Services\NewsAggregator\NewsAggregatorService;
use App\Services\NewsAggregator\Adapters\NewsApiAdapter;
use App\Services\NewsAggregator\Adapters\GuardianAdapter;
use App\Services\NewsAggregator\Adapters\NYTimesAdapter;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

uses(Tests\TestCase::class, RefreshDatabase::class);

describe('NewsAggregatorService', function () {

    beforeEach(function () {
        // Mock API configurations
        Config::set('services.newsapi.key', 'test-newsapi-key');
        Config::set('services.newsapi.url', 'https://newsapi.org/v2');
        Config::set('services.guardian.key', 'test-guardian-key');
        Config::set('services.guardian.url', 'https://content.guardianapis.com');
        Config::set('services.nytimes.key', 'test-nyt-key');
        Config::set('services.nytimes.url', 'https://api.nytimes.com/svc');
    });

    it('initializes with configured services', function () {
        $aggregator = new NewsAggregatorService();

        $services = $aggregator->getAvailableServices();
        expect($services)->toContain('newsapi');
        expect($services)->toContain('guardian');
        expect($services)->toContain('nytimes');
    });

    it('can get specific service', function () {
        $aggregator = new NewsAggregatorService();

        $newsApiService = $aggregator->getService('newsapi');
        expect($newsApiService)->toBeInstanceOf(NewsApiAdapter::class);

        $guardianService = $aggregator->getService('guardian');
        expect($guardianService)->toBeInstanceOf(GuardianAdapter::class);

        $nytService = $aggregator->getService('nytimes');
        expect($nytService)->toBeInstanceOf(NYTimesAdapter::class);
    });

    it('returns null for non-existent service', function () {
        $aggregator = new NewsAggregatorService();

        $service = $aggregator->getService('non-existent');
        expect($service)->toBeNull();
    });

    it('cleans up old articles', function () {
        // Create old articles
        Article::factory()->count(5)->create([
            'published_at' => now()->subDays(40)
        ]);

        // Create recent articles
        Article::factory()->count(3)->create([
            'published_at' => now()->subDays(10)
        ]);

        expect(Article::count())->toBe(8);

        $aggregator = new NewsAggregatorService();
        $deletedCount = $aggregator->cleanupOldArticles(30);

        expect($deletedCount)->toBe(5);
        expect(Article::count())->toBe(3);
    });

    describe('Article aggregation with mocked APIs', function () {

        it('aggregates articles from all services', function () {
            // Mock NewsAPI response
            Http::fake([
                'newsapi.org/*' => Http::response([
                    'articles' => [
                        [
                            'title' => 'NewsAPI Test Article',
                            'content' => 'Test content from NewsAPI',
                            'description' => 'Test description',
                            'url' => 'https://example.com/newsapi/1',
                            'urlToImage' => 'https://example.com/image1.jpg',
                            'publishedAt' => '2023-09-06T12:00:00Z',
                            'source' => ['id' => 'test-source', 'name' => 'Test Source']
                        ]
                    ]
                ]),
                'content.guardianapis.com/*' => Http::response([
                    'response' => [
                        'results' => [
                            [
                                'id' => 'guardian-test-1',
                                'webTitle' => 'Guardian Test Article',
                                'webUrl' => 'https://example.com/guardian/1',
                                'webPublicationDate' => '2023-09-06T12:00:00Z',
                                'sectionName' => 'Technology',
                                'fields' => [
                                    'headline' => 'Guardian Test Article',
                                    'body' => 'Test content from Guardian',
                                    'byline' => 'Guardian Author',
                                    'thumbnail' => 'https://example.com/guardian-image.jpg',
                                    'trailText' => 'Guardian test description'
                                ]
                            ]
                        ]
                    ]
                ]),
                'api.nytimes.com/*' => Http::response([
                    'response' => [
                        'docs' => [
                            [
                                '_id' => 'nyt-test-1',
                                'headline' => ['main' => 'NYT Test Article'],
                                'web_url' => 'https://example.com/nyt/1',
                                'pub_date' => '2023-09-06T12:00:00Z',
                                'section_name' => 'Technology',
                                'abstract' => 'Test abstract from NYT',
                                'lead_paragraph' => 'Test content from NYT',
                                'snippet' => 'Test snippet from NYT',
                                'byline' => ['original' => 'By NYT Author'],
                                'multimedia' => [
                                    [
                                        'type' => 'image',
                                        'url' => 'images/nyt-image.jpg'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ])
            ]);

            $aggregator = new NewsAggregatorService();
            $result = $aggregator->aggregateArticles();

            expect($result['stats']['total_fetched'])->toBe(3);
            expect($result['stats']['total_saved'])->toBe(3);
            expect($result['stats']['duplicates_skipped'])->toBe(0);
            expect($result['stats']['errors'])->toBe(0);

            expect(Article::count())->toBe(3);

            // Check if articles from different sources were saved
            expect(Article::where('source_name', 'NewsAPI')->count())->toBe(1);
            expect(Article::where('source_name', 'The Guardian')->count())->toBe(1);
            expect(Article::where('source_name', 'New York Times')->count())->toBe(1);

            // Check if categories were properly mapped
            $guardianArticle = Article::where('source_name', 'The Guardian')->first();
            expect($guardianArticle->category)->toBe('technology');

            $nytArticle = Article::where('source_name', 'New York Times')->first();
            expect($nytArticle->category)->toBe('technology');

            // Check if image URLs were properly processed
            $nytArticle = Article::where('source_name', 'New York Times')->first();
            expect($nytArticle->image_url)->toBe('https://www.nytimes.com/images/nyt-image.jpg');
        });

        it('handles duplicate articles correctly', function () {
            // Create existing article
            Article::factory()->create([
                'url' => 'https://example.com/duplicate-test'
            ]);

            // Mock API to return the same article
            Http::fake([
                'newsapi.org/*' => Http::response([
                    'articles' => [
                        [
                            'title' => 'Duplicate Test Article',
                            'content' => 'Test content',
                            'url' => 'https://example.com/duplicate-test',
                            'publishedAt' => '2023-09-06T12:00:00Z',
                            'source' => ['name' => 'Test Source']
                        ]
                    ]
                ])
            ]);

            $aggregator = new NewsAggregatorService();
            $result = $aggregator->aggregateArticles();

            expect($result['stats']['total_fetched'])->toBe(1);
            expect($result['stats']['total_saved'])->toBe(0);
            expect($result['stats']['duplicates_skipped'])->toBe(1);

            // Should still have only 1 article
            expect(Article::count())->toBe(1);
        });

        it('handles API errors gracefully', function () {
            Http::fake([
                'newsapi.org/*' => Http::response([], 500),
                'content.guardianapis.com/*' => Http::response([], 403),
                'api.nytimes.com/*' => Http::response([], 404)
            ]);

            $aggregator = new NewsAggregatorService();
            $result = $aggregator->aggregateArticles();

            expect($result['stats']['total_fetched'])->toBe(0);
            expect($result['stats']['total_saved'])->toBe(0);
            expect($result['stats']['errors'])->toBe(0); // HTTP errors are handled gracefully, not counted as errors
            expect(Article::count())->toBe(0);
        });

        it('validates article data using DTOs', function () {
            // Mock API to return invalid article (missing URL)
            Http::fake([
                'newsapi.org/*' => Http::response([
                    'articles' => [
                        [
                            'title' => 'Invalid Article',
                            'content' => 'Test content',
                            // Missing URL - should be skipped
                            'publishedAt' => '2023-09-06T12:00:00Z',
                            'source' => ['name' => 'Test Source']
                        ]
                    ]
                ])
            ]);

            $aggregator = new NewsAggregatorService();
            $result = $aggregator->aggregateArticles();

            expect($result['stats']['total_fetched'])->toBe(1);
            expect($result['stats']['total_saved'])->toBe(0);
            expect($result['stats']['errors'])->toBe(0);
            expect(Article::count())->toBe(0);
        });
    });
});
