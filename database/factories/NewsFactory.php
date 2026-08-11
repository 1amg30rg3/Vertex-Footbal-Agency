<?php

namespace Database\Factories;

use App\Models\News;
use App\Support\Locales;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<News>
 */
class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition(): array
    {
        $title = rtrim(fake()->sentence(6), '.');

        return [
            'title' => $this->translated($title),
            'excerpt' => $this->translated(fake()->sentence(18)),
            'body' => $this->translated(collect(fake()->paragraphs(5))
                ->map(fn (string $paragraph) => "<p>{$paragraph}</p>")
                ->implode('')),
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-8 months', 'now'),
            'is_featured' => false,
            'featured_order' => 0,
            'views' => fake()->numberBetween(0, 900),
        ];
    }

    public function featured(int $order = 0): static
    {
        return $this->state(fn () => ['is_featured' => true, 'featured_order' => $order]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft', 'published_at' => null]);
    }

    public function scheduled(): static
    {
        return $this->state(fn () => [
            'status' => 'scheduled',
            'published_at' => fake()->dateTimeBetween('+2 days', '+3 weeks'),
        ]);
    }

    protected function translated(string $value): array
    {
        return array_fill_keys(Locales::codes(), $value);
    }
}
