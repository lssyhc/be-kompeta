<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(ContentTypeSeeder::class);
        $this->call(UserProfileSeeder::class);
        $this->call(JobVacancySeeder::class);
        $this->call(StudentPortfolioSeeder::class);
        $this->call(BlogArticleSeeder::class);
        $this->call(PartnershipProposalSeeder::class);
        $this->call(StudentJobApplicationSeeder::class);
    }
}
