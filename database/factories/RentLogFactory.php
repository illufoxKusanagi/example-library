<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\RentLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RentLog>
 */
class RentLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'rent_date' => now()->toDateString(),
            'return_date' => now()->addDays(7)->toDateString(),
            'actual_return_date' => null,
            'status' => 'active',
        ];
    }

    /**
     * State for returned loans.
     */
    public function returned(): static
    {
        return $this->state(fn (array $attributes) => [
            'actual_return_date' => now()->toDateString(),
            'status' => 'returned',
        ]);
    }

    /**
     * State for overdue loans.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'rent_date' => now()->subDays(10)->toDateString(),
            'return_date' => now()->subDays(3)->toDateString(),
            'actual_return_date' => null,
            'status' => 'active',
        ]);
    }
}
