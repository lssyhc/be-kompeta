<?php

namespace Database\Seeders;

use App\Models\StudentAchievement;
use App\Models\StudentApplication;
use App\Models\StudentExperience;
use App\Models\StudentProfile;
use App\Models\StudentSkill;
use Illuminate\Database\Seeder;

class StudentPortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $studentA1 = StudentProfile::query()->where('nisn', '0001234567')->firstOrFail();
        $studentA2 = StudentProfile::query()->where('nisn', '0001234568')->firstOrFail();
        $studentB1 = StudentProfile::query()->where('nisn', '0002345678')->firstOrFail();

        $this->syncSkills($studentA1->id, ['MYOB', 'Excel', 'Komunikasi']);
        $this->syncSkills($studentA2->id, ['Public Speaking', 'Bahasa Inggris', 'Leadership']);
        $this->syncSkills($studentB1->id, ['Administrasi', 'Customer Service']);

        $this->syncExperiences($studentA1->id, [
            [
                'title' => 'Asisten Laboratorium Komputer',
                'description' => 'Membantu guru dalam persiapan praktikum dan troubleshooting perangkat.',
                'position' => 'Asisten Lab',
                'company_name' => 'SMA Negeri 1 Bogor',
                'start_date' => now()->subMonths(10)->toDateString(),
                'end_date' => now()->subMonths(4)->toDateString(),
            ],
        ]);

        $this->syncExperiences($studentA2->id, [
            [
                'title' => 'Panitia Event Edu Fair',
                'description' => 'Menangani publikasi acara dan koordinasi peserta sekolah.',
                'position' => 'Staf Publikasi',
                'company_name' => 'Komunitas Edu Bogor',
                'start_date' => now()->subMonths(6)->toDateString(),
                'end_date' => now()->subMonths(5)->toDateString(),
            ],
        ]);

        $this->syncExperiences($studentB1->id, [
            [
                'title' => 'Magang Admin Data',
                'description' => 'Input data produk dan rekap laporan mingguan.',
                'position' => 'Admin Intern',
                'company_name' => 'CV Data Mandiri',
                'start_date' => now()->subMonths(8)->toDateString(),
                'end_date' => now()->subMonths(6)->toDateString(),
            ],
        ]);

        $this->syncAchievements($studentA1->id, [
            [
                'title' => 'Juara 1 Olimpiade Matematika',
                'description' => 'OSN Tingkat Nasional 2024',
                'achievement_date' => now()->subMonths(6)->toDateString(),
                'institution_name' => 'Kementerian Pendidikan',
            ],
        ]);

        $this->syncAchievements($studentA2->id, [
            [
                'title' => 'Juara 2 Debat Bahasa Inggris',
                'description' => 'Kompetisi Nasional Bahasa Inggris 2024',
                'achievement_date' => now()->subMonths(4)->toDateString(),
                'institution_name' => 'British Council',
            ],
        ]);

        $this->syncAchievements($studentB1->id, [
            [
                'title' => 'Penghargaan Siswa Berprestasi',
                'description' => 'Penghargaan sekolah untuk prestasi akademik dan karakter.',
                'achievement_date' => now()->subMonths(3)->toDateString(),
                'institution_name' => 'SMA Negeri 2 Jakarta',
            ],
        ]);

        StudentApplication::query()->updateOrCreate(
            [
                'student_profile_id' => $studentA1->id,
                'company_name' => 'PT Surya Data Nusantara',
                'role_type' => 'Data Entry Operator',
            ],
            [
                'applied_at' => now()->subMonths(2),
                'status' => 'applied',
                'job_vacancy_id' => null,
                'mitra_user_id' => null,
                'cv_path' => null,
                'cover_letter' => null,
            ]
        );
    }

    private function syncSkills(int $studentProfileId, array $titles): void
    {
        StudentSkill::query()->where('student_profile_id', $studentProfileId)->delete();

        foreach ($titles as $title) {
            StudentSkill::query()->create([
                'student_profile_id' => $studentProfileId,
                'title' => $title,
            ]);
        }
    }

    private function syncExperiences(int $studentProfileId, array $experiences): void
    {
        StudentExperience::query()->where('student_profile_id', $studentProfileId)->delete();

        foreach ($experiences as $experience) {
            StudentExperience::query()->create([
                'student_profile_id' => $studentProfileId,
                'title' => $experience['title'],
                'description' => $experience['description'],
                'position' => $experience['position'],
                'company_name' => $experience['company_name'],
                'start_date' => $experience['start_date'],
                'end_date' => $experience['end_date'],
            ]);
        }
    }

    private function syncAchievements(int $studentProfileId, array $achievements): void
    {
        StudentAchievement::query()->where('student_profile_id', $studentProfileId)->delete();

        foreach ($achievements as $achievement) {
            StudentAchievement::query()->create([
                'student_profile_id' => $studentProfileId,
                'title' => $achievement['title'],
                'description' => $achievement['description'],
                'achievement_date' => $achievement['achievement_date'],
                'institution_name' => $achievement['institution_name'],
            ]);
        }
    }
}
