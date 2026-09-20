<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Book>
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            //
            'book_code' => 'BK'.fake()->unique()->numerify('###'),
            'title' => $title,
            'author' => fake()->name(),
            'slug' => Str::slug($title),
            'synopsis' => fake()->paragraphs(2, true),
            'published_year' => fake()->year(),
            'status' => 'available',
        ];
    }

    /**
     * Indicate that the book is unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'unavailable',
        ]);
    }
}
