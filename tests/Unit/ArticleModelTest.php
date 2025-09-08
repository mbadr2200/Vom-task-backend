<?php

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

describe('Article Model', function () {

    it('can create an article', function () {
        $article = Article::factory()->create([
            'title' => 'Test Article',
            'url' => 'https://example.com/test',
            'source_name' => 'Test Source'
        ]);

        expect($article)->toBeInstanceOf(Article::class);
        expect($article->title)->toBe('Test Article');
        expect($article->url)->toBe('https://example.com/test');
        expect($article->source_name)->toBe('Test Source');
    });

    it('has proper fillable attributes', function () {
        $fillable = [
            'title',
            'content',
            'description',
            'url',
            'image_url',
            'source_name',
            'source_id',
            'category',
            'published_at',
        ];

        $article = new Article();
        expect($article->getFillable())->toBe($fillable);
    });

    it('casts published_at to datetime', function () {
        $article = Article::factory()->create();

        expect($article->published_at)->toBeInstanceOf(\Carbon\Carbon::class);
    });

    describe('Scopes', function () {

        it('filters by source using bySource scope', function () {
            Article::factory()->newsapi()->count(3)->create();
            Article::factory()->guardian()->count(2)->create();

            $newsApiArticles = Article::bySource('NewsAPI')->get();
            $guardianArticles = Article::bySource('The Guardian')->get();

            expect($newsApiArticles)->toHaveCount(3);
            expect($guardianArticles)->toHaveCount(2);
        });

        it('filters by category using byCategory scope', function () {
            Article::factory()->category('technology')->count(4)->create();
            Article::factory()->category('business')->count(2)->create();

            $techArticles = Article::byCategory('technology')->get();
            $businessArticles = Article::byCategory('business')->get();

            expect($techArticles)->toHaveCount(4);
            expect($businessArticles)->toHaveCount(2);
        });

        it('filters by date range using byDateRange scope', function () {
            $today = now();
            $yesterday = $today->copy()->subDay();
            $twoDaysAgo = $today->copy()->subDays(2);
            $threeDaysAgo = $today->copy()->subDays(3);

            Article::factory()->create(['published_at' => $today]);
            Article::factory()->create(['published_at' => $yesterday]);
            Article::factory()->create(['published_at' => $twoDaysAgo]);
            Article::factory()->create(['published_at' => $threeDaysAgo]);

            // Test from date only
            $fromYesterday = Article::byDateRange($yesterday->toDateString())->get();
            expect($fromYesterday)->toHaveCount(2);

            // Test to date only
            $toYesterday = Article::byDateRange(null, $yesterday->toDateString())->get();
            expect($toYesterday)->toHaveCount(3);

            // Test both dates
            $betweenDates = Article::byDateRange(
                $twoDaysAgo->toDateString(),
                $yesterday->toDateString()
            )->get();
            expect($betweenDates)->toHaveCount(2);
        });

        it('orders by latest using latestByPublished scope', function () {
            $oldest = Article::factory()->create(['published_at' => now()->subDays(3)]);
            $newest = Article::factory()->create(['published_at' => now()]);
            $middle = Article::factory()->create(['published_at' => now()->subDay()]);

            $articles = Article::latestByPublished()->get();

            expect($articles->first()->id)->toBe($newest->id);
            expect($articles->last()->id)->toBe($oldest->id);
        });
    });

    describe('Validation', function () {

        it('requires a title', function () {
            expect(function () {
                Article::create([
                    'url' => 'https://example.com',
                    'source_name' => 'Test Source',
                    'published_at' => now()
                ]);
            })->toThrow(\Illuminate\Database\QueryException::class);
        });

        it('requires a unique url', function () {
            Article::factory()->create(['url' => 'https://example.com/unique']);

            expect(function () {
                Article::create([
                    'title' => 'Test Article',
                    'url' => 'https://example.com/unique',
                    'source_name' => 'Test Source',
                    'published_at' => now()
                ]);
            })->toThrow(\Illuminate\Database\QueryException::class);
        });

        it('requires a source name', function () {
            expect(function () {
                Article::create([
                    'title' => 'Test Article',
                    'url' => 'https://example.com',
                    'published_at' => now()
                ]);
            })->toThrow(\Illuminate\Database\QueryException::class);
        });
    });
});

