<?php

namespace Database\Seeders;

use App\Models\ContentType;
use Illuminate\Database\Seeder;

class ContentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'News', 'slug' => 'news'],
            ['name' => 'Tips', 'slug' => 'tips'],
            ['name' => 'Career Insight', 'slug' => 'career-insight'],
            ['name' => 'Success Story', 'slug' => 'success-story'],
        ];

        foreach ($types as $type) {
            ContentType::query()->updateOrCreate(
                ['slug' => $type['slug']],
                [
                    'name' => $type['name'],
                    'is_active' => true,
                ]
            );
        }
    }
}
