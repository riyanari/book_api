<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Self Improvement',
            'Bisnis',
            'Psikologi',
            'Teknologi',
            'Novel',
            'Agama',
            'Pendidikan',
            'Keuangan',
            'Produktivitas',
            'Karier',
            'Kesehatan',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }
    }
}