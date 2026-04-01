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
                'cv_path' => $cvA1,
                'cover_letter' => 'Saya memiliki ketelitian dan ketertarikan pada bidang akuntansi operasional.',
                'status' => StudentApplication::STATUS_SUBMITTED,
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
                'cv_path' => $cvB1,
                'cover_letter' => 'Saya siap berkontribusi sebagai data entry operator dan belajar cepat.',
                'status' => StudentApplication::STATUS_SUBMITTED,
                'applied_at' => now()->subDays(2),
            ]
        );
    }

    private function storeLocalFile(string $path, string $label): string
    {
        $binary = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj 2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj 3 0 obj<</Type/Page/MediaBox[0 0 612 792]>>endobj\nxref\n0 4\n0000000000 65535 f\n0000000009 00000 n\n0000000058 00000 n\n0000000115 00000 n\ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n190\n%%EOF";

        Storage::disk('local')->put($path, $binary);

        return $path;
    }
}
