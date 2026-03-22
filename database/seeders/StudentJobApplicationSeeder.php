<?php

namespace Database\Seeders;

use App\Models\JobVacancy;
use App\Models\StudentApplication;
use App\Models\StudentProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class StudentJobApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $studentA1 = StudentProfile::query()->where('nisn', '0001234567')->firstOrFail();
        $studentB1 = StudentProfile::query()->where('nisn', '0002345678')->firstOrFail();

        $vacancyAccounting = JobVacancy::query()->where('slug', 'accounting-staff-1602be')->firstOrFail();
        $vacancyDataEntry = JobVacancy::query()->where('slug', 'data-entry-operator-bandung')->firstOrFail();

        $cvA1 = $this->storeLocalFile('student-applications/cv/cv-budi-santoso.pdf', 'CV Budi Santoso');
        $cvB1 = $this->storeLocalFile('student-applications/cv/cv-ahmad-wijaya.pdf', 'CV Ahmad Wijaya');

        StudentApplication::query()->updateOrCreate(
            [
                'student_profile_id' => $studentA1->id,
                'job_vacancy_id' => $vacancyAccounting->id,
            ],
            [
                'mitra_user_id' => $vacancyAccounting->mitra_user_id,
                'company_name' => $vacancyAccounting->resolveMitraName() ?? 'PT Binajasa Sumber Sarana',
                'role_type' => $vacancyAccounting->position_name,
                'submitted_at' => now()->subDays(4)->toDateString(),
                'submit_status' => 'submitted',
                'cv_path' => $cvA1,
                'cover_letter' => 'Saya memiliki ketelitian dan ketertarikan pada bidang akuntansi operasional.',
                'status' => StudentApplication::STATUS_APPLIED,
                'applied_at' => now()->subDays(4),
            ]
        );

        StudentApplication::query()->updateOrCreate(
            [
                'student_profile_id' => $studentB1->id,
                'job_vacancy_id' => $vacancyDataEntry->id,
            ],
            [
                'mitra_user_id' => $vacancyDataEntry->mitra_user_id,
                'company_name' => $vacancyDataEntry->resolveMitraName() ?? 'PT Surya Data Nusantara',
                'role_type' => $vacancyDataEntry->position_name,
                'submitted_at' => now()->subDays(2)->toDateString(),
                'submit_status' => 'submitted',
                'cv_path' => $cvB1,
                'cover_letter' => 'Saya siap berkontribusi sebagai data entry operator dan belajar cepat.',
                'status' => StudentApplication::STATUS_APPLIED,
                'applied_at' => now()->subDays(2),
            ]
        );
    }

    private function storeLocalFile(string $path, string $content): string
    {
        Storage::disk('local')->put($path, $content);

        return $path;
    }
}
