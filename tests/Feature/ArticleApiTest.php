<?php

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Articles API', function () {

    describe('GET /api/articles', function () {

        it('returns empty response when no articles exist', function () {
            $response = $this->getJson('/api/articles');

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [],
                        'pagination' => [
                            'total' => 0,
                            'current_page' => 1
                        ]
                    ]);
        });

        it('returns all articles when no filters are applied', function () {
            Article::factory()->count(5)->create();

            $response = $this->getJson('/api/articles');

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'data' => [
                            '*' => [
                                'id',
                                'title',
                                'content',
                                'description',
                                'url',
                                'source_name',
                                'category',
                                'published_at'
                            ]
                        ],
                        'pagination'
                    ]);

            expect($response->json('data'))->toHaveCount(5);
            expect($response->json('pagination.total'))->toBe(5);
        });

        it('filters articles by source', function () {
            Article::factory()->newsapi()->count(3)->create();
            Article::factory()->guardian()->count(2)->create();

            $response = $this->getJson('/api/articles?source=NewsAPI');

            $response->assertStatus(200);
            expect($response->json('data'))->toHaveCount(3);

            foreach ($response->json('data') as $article) {
                expect($article['source_name'])->toBe('NewsAPI');
            }
        });

        it('filters articles by category', function () {
            Article::factory()->category('technology')->count(4)->create();
            Article::factory()->category('business')->count(3)->create();

            $response = $this->getJson('/api/articles?category=technology');

            $response->assertStatus(200);
            expect($response->json('data'))->toHaveCount(4);

            foreach ($response->json('data') as $article) {
                expect($article['category'])->toBe('technology');
            }
        });

        it('filters articles by date range', function () {
            $today = now();
            $yesterday = $today->copy()->subDay();
            $twoDaysAgo = $today->copy()->subDays(2);

            Article::factory()->create(['published_at' => $today]);
            Article::factory()->create(['published_at' => $yesterday]);
            Article::factory()->create(['published_at' => $twoDaysAgo]);

            $response = $this->getJson('/api/articles?from_date=' . $yesterday->toDateString());

            $response->assertStatus(200);
            expect($response->json('data'))->toHaveCount(2);
        });

        it('paginates results correctly', function () {
            Article::factory()->count(25)->create();

            $response = $this->getJson('/api/articles?per_page=10');

            $response->assertStatus(200);
            expect($response->json('data'))->toHaveCount(10);
            expect($response->json('pagination.total'))->toBe(25);
            expect($response->json('pagination.last_page'))->toBe(3);
        });

        it('validates pagination parameters', function () {
            $response = $this->getJson('/api/articles?per_page=150');

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['per_page']);
        });

        it('validates date parameters', function () {
            $response = $this->getJson('/api/articles?from_date=invalid-date');

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['from_date']);
        });

        it('validates date range order', function () {
            $response = $this->getJson('/api/articles?from_date=2023-12-01&to_date=2023-11-01');

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['to_date']);
        });
    });

    describe('GET /api/articles/{id}', function () {

        it('returns a specific article', function () {
            $article = Article::factory()->create();

            $response = $this->getJson("/api/articles/{$article->id}");

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'id' => $article->id,
                            'title' => $article->title,
                            'url' => $article->url,
                        ]
                    ]);
        });

        it('returns 404 for non-existent article', function () {
            $response = $this->getJson('/api/articles/999');

            $response->assertStatus(404);
        });
    });

    describe('GET /api/articles/sources', function () {

        it('returns available sources', function () {
            Article::factory()->newsapi()->create();
            Article::factory()->guardian()->create();
            Article::factory()->nytimes()->create();

            $response = $this->getJson('/api/articles/sources');

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'data'
                    ]);

            $sources = $response->json('data');
            expect($sources)->toContain('NewsAPI');
            expect($sources)->toContain('The Guardian');
            expect($sources)->toContain('New York Times');
        });
    });

    describe('GET /api/articles/categories', function () {

        it('returns available categories', function () {
            Article::factory()->category('technology')->create();
            Article::factory()->category('business')->create();
            Article::factory()->category('sports')->create();

            $response = $this->getJson('/api/articles/categories');

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'data'
                    ]);

            $categories = $response->json('data');
            expect($categories)->toContain('technology');
            expect($categories)->toContain('business');
            expect($categories)->toContain('sports');
        });
    });
});
