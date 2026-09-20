<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Action',
            'Adventure',
            'Adult Cast',
            'Award Winning',
            'Comedy',
            'Drama',
            'Fantasy',
            'Gag Humor',
            'Gore',
            'Harem',
            'Horror',
            'Isekai',
            'Mystery',
            'Parody',
            'Racing',
            'Reincarnation',
            'Romance',
            'School',
            'Showbiz',
            'Supernatural',
            'Survival',
            'Suspense',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }
    }
}
