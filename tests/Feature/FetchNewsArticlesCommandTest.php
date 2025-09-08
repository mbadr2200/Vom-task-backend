<?php

use App\Models\Article;
use App\Services\NewsAggregator\NewsAggregatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

describe('FetchNewsArticles Command', function () {

    beforeEach(function () {
        // Mock API configurations
        Config::set('services.newsapi.key', 'test-key');
        Config::set('services.newsapi.url', 'https://newsapi.org/v2');
        Config::set('services.guardian.key', 'test-key');
        Config::set('services.guardian.url', 'https://content.guardianapis.com');
        Config::set('services.nytimes.key', 'test-key');
        Config::set('services.nytimes.url', 'https://api.nytimes.com/svc');
    });

    it('executes successfully with default parameters', function () {
        // Mock successful API responses
        Http::fake([
            'newsapi.org/*' => Http::response(['articles' => []]),
            'content.guardianapis.com/*' => Http::response(['response' => ['results' => []]]),
            'api.nytimes.com/*' => Http::response(['response' => ['docs' => []]])
        ]);

        $this->artisan('news:fetch')
             ->expectsOutput('🚀 Starting news aggregation...')
             ->expectsOutputToContain('📊 Aggregation Results:')
             ->expectsOutputToContain('✅ News aggregation completed')
             ->assertExitCode(0);
    });

    it('can fetch from specific source', function () {
        Http::fake([
            'newsapi.org/*' => Http::response([
                'articles' => [
                    [
                        'title' => 'Test Article',
                        'content' => 'Test content',
                        'url' => 'https://example.com/test',
                        'publishedAt' => '2023-09-06T12:00:00Z',
                        'source' => ['name' => 'Test Source']
                    ]
                ]
            ])
        ]);

        $this->artisan('news:fetch --source=newsapi')
             ->expectsOutputToContain('Source: newsapi')
             ->expectsOutputToContain('Articles Fetched: 1')
             ->assertExitCode(0);

        expect(Article::count())->toBe(1);
    });

    it('can fetch specific category', function () {
        Http::fake([
            'newsapi.org/*' => Http::response(['articles' => []]),
            'content.guardianapis.com/*' => Http::response(['response' => ['results' => []]]),
            'api.nytimes.com/*' => Http::response(['response' => ['docs' => []]])
        ]);

        $this->artisan('news:fetch --category=technology')
             ->expectsOutputToContain('Category: technology')
             ->assertExitCode(0);
    });

    it('can specify number of days to fetch', function () {
        Http::fake([
            'newsapi.org/*' => Http::response(['articles' => []]),
            'content.guardianapis.com/*' => Http::response(['response' => ['results' => []]]),
            'api.nytimes.com/*' => Http::response(['response' => ['docs' => []]])
        ]);

        $this->artisan('news:fetch --days=7')
             ->expectsOutputToContain('Time Range: Last 7 day(s)')
             ->assertExitCode(0);
    });

    it('can cleanup old articles', function () {
        // Create old articles
        Article::factory()->count(5)->create([
            'published_at' => now()->subDays(40)
        ]);

        Http::fake([
            'newsapi.org/*' => Http::response(['articles' => []]),
            'content.guardianapis.com/*' => Http::response(['response' => ['results' => []]]),
            'api.nytimes.com/*' => Http::response(['response' => ['docs' => []]])
        ]);

        expect(Article::count())->toBe(5);

        $this->artisan('news:fetch --cleanup')
             ->expectsOutputToContain('🧹 Cleaning up old articles...')
             ->expectsOutputToContain('Deleted 5 old articles')
             ->assertExitCode(0);

        expect(Article::count())->toBe(0);
    });

    it('handles API errors gracefully', function () {
        Http::fake([
            'newsapi.org/*' => Http::response([], 500),
            'content.guardianapis.com/*' => Http::response([], 500),
            'api.nytimes.com/*' => Http::response([], 500)
        ]);

        $this->artisan('news:fetch')
             ->expectsOutputToContain('📊 Aggregation Results:')
             ->expectsOutputToContain('Articles Fetched: 0')
             ->assertExitCode(0); // Should still exit successfully even with API errors
    });

    it('displays execution time', function () {
        Http::fake([
            'newsapi.org/*' => Http::response(['articles' => []]),
            'content.guardianapis.com/*' => Http::response(['response' => ['results' => []]]),
            'api.nytimes.com/*' => Http::response(['response' => ['docs' => []]])
        ]);

        $this->artisan('news:fetch')
             ->expectsOutputToContain('completed in')
             ->expectsOutputToContain('s')
             ->assertExitCode(0);
    });

    it('shows duplicate statistics', function () {
        // Create existing article
        Article::factory()->create([
            'url' => 'https://example.com/existing'
        ]);

        Http::fake([
            'newsapi.org/*' => Http::response([
                'articles' => [
                    [
                        'title' => 'Existing Article',
                        'content' => 'Test content',
                        'url' => 'https://example.com/existing',
                        'publishedAt' => '2023-09-06T12:00:00Z',
                        'source' => ['name' => 'Test Source']
                    ]
                ]
            ]),
            'content.guardianapis.com/*' => Http::response(['response' => ['results' => []]]),
            'api.nytimes.com/*' => Http::response(['response' => ['docs' => []]])
        ]);

        $this->artisan('news:fetch')
             ->expectsOutputToContain('Duplicates Skipped: 1')
             ->assertExitCode(0);
    });
});

