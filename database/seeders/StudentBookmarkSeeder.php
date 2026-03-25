<?php

namespace Database\Seeders;

use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentBookmarkSeeder extends Seeder
{
    public function run(): void
    {
        $studentA = User::query()
            ->where('role', User::ROLE_SISWA)
            ->whereHas('studentProfile', fn ($q) => $q->where('nisn', '0001234567'))
            ->firstOrFail();

        $studentB = User::query()
            ->where('role', User::ROLE_SISWA)
            ->whereHas('studentProfile', fn ($q) => $q->where('nisn', '0002345678'))
            ->firstOrFail();

        $vacancySlugs = [
            'accounting-staff-1602be',
            'data-entry-operator-bandung',
            'junior-qa-tester-remote',
            'baker-assistant-semarang',
        ];

        $vacancyIds = JobVacancy::query()
            ->whereIn('slug', $vacancySlugs)
            ->pluck('id', 'slug');

        // Siswa A bookmarks 3 lowongan
        $studentABookmarks = [
            'accounting-staff-1602be',
            'junior-qa-tester-remote',
            'baker-assistant-semarang',
        ];

        foreach ($studentABookmarks as $slug) {
            if ($vacancyIds->has($slug)) {
                $studentA->bookmarkedJobVacancies()->syncWithoutDetaching([$vacancyIds[$slug]]);
            }
        }

        // Siswa B bookmarks 2 lowongan
        $studentBBookmarks = [
            'data-entry-operator-bandung',
            'accounting-staff-1602be',
        ];

        foreach ($studentBBookmarks as $slug) {
            if ($vacancyIds->has($slug)) {
                $studentB->bookmarkedJobVacancies()->syncWithoutDetaching([$vacancyIds[$slug]]);
            }
        }
    }
}
