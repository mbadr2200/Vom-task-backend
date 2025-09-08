<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sources = ['NewsAPI', 'The Guardian', 'New York Times'];
        $categories = ['business', 'technology', 'science', 'sports', 'entertainment', 'health', 'general'];

        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'description' => $this->faker->paragraph(2),
            'url' => $this->faker->unique()->url(),
            'image_url' => $this->faker->optional(0.7)->imageUrl(640, 480, 'news'),
            'source_name' => $this->faker->randomElement($sources),
            'source_id' => $this->faker->optional(0.5)->slug(2),
            'category' => $this->faker->randomElement($categories),
            'published_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Create article from NewsAPI source.
     */
    public function newsapi(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_name' => 'NewsAPI',
        ]);
    }

    /**
     * Create article from Guardian source.
     */
    public function guardian(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_name' => 'The Guardian',
        ]);
    }

    /**
     * Create article from NYT source.
     */
    public function nytimes(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_name' => 'New York Times',
        ]);
    }

    /**
     * Create article with specific category.
     */
    public function category(string $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => $category,
        ]);
    }
}
