<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\RentLog;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@library.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Client / Member user
        $client = User::firstOrCreate(
            ['email' => 'client@library.com'],
            [
                'name' => 'Client Member',
                'password' => Hash::make('password'),
                'role' => 'client',
                'email_verified_at' => now(),
            ]
        );

        // Default test user for compatibility
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            CategorySeeder::class,
            BookSeeder::class,
        ]);

        // Seed sample loan record for demonstration
        $rentedBook = Book::where('book_code', 'BK001')->first();
        if ($rentedBook) {
            RentLog::firstOrCreate(
                ['book_id' => $rentedBook->id, 'actual_return_date' => null],
                [
                    'user_id' => $client->id,
                    'rent_date' => now()->subDays(2)->toDateString(),
                    'return_date' => now()->addDays(5)->toDateString(),
                    'status' => 'rented',
                ]
            );
            $rentedBook->update(['status' => 'unavailable']);
        }
    }
}
