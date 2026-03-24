<?php

namespace Tests\Feature;

use App\Models\CompanyProfile;
use App\Models\JobVacancy;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExploreApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_explore_index_returns_job_cards(): void
    {
        $mitra = $this->createCompanyMitra('PT Jelajah Karir', '1234567890111');
        $job = $this->createVacancy($mitra->id, [
            'position_name' => 'Accounting Staff',
            'province' => 'DKI Jakarta',
            'salary_min' => 5700000,
            'salary_max' => 5800000,
            'is_salary_hidden' => false,
        ], ['MYOB', 'Excel'], ['BPJS Kesehatan']);

        $response = $this->getJson('/api/public/explore/jobs');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.slug', $job->slug)
            ->assertJsonPath('data.0.position_name', 'Accounting Staff')
            ->assertJsonPath('data.0.mitra_name', 'PT Jelajah Karir')
            ->assertJsonPath('meta.pagination.total', 1);

        $response->assertJsonMissingPath('data.0.company_name');
    }

    public function test_explore_search_can_find_by_skill_and_mitra_name(): void
    {
        $companyMitra = $this->createCompanyMitra('PT Analitik Nusantara', '1234567890112');
        $umkmMitra = $this->createUmkmMitra('Kopi Rasa Lokal', '3201234567899001');

        $this->createVacancy($companyMitra->id, [
            'position_name' => 'Data Entry Operator',
            'province' => 'Jawa Barat',
        ], ['Data Entry'], ['Laptop Kerja']);

        $this->createVacancy($umkmMitra->id, [
            'position_name' => 'Barista Freelance',
            'job_type' => JobVacancy::JOB_TYPE_FREELANCE,
            'province' => 'Jawa Tengah',
        ], ['Latte Art'], ['Uang Makan']);

        $bySkill = $this->getJson('/api/public/explore/jobs?q=latte');
        $byMitra = $this->getJson('/api/public/explore/jobs?q=analitik');

        $bySkill->assertStatus(200)
            ->assertJsonPath('data.0.position_name', 'Barista Freelance');

        $byMitra->assertStatus(200)
            ->assertJsonPath('data.0.position_name', 'Data Entry Operator');
    }

    public function test_explore_filters_work_for_job_type_work_policy_province_experience_and_updated_within(): void
    {
        $mitra = $this->createCompanyMitra('PT Filter Uji', '1234567890113');

        $this->createVacancy($mitra->id, [
            'position_name' => 'QA Remote Fresh Graduate',
            'job_type' => JobVacancy::JOB_TYPE_FULL_TIME,
            'work_policy' => JobVacancy::WORK_POLICY_REMOTE,
            'experience_level' => JobVacancy::EXPERIENCE_FRESH_GRADUATE,
            'province' => 'Jawa Barat',
            'updated_at' => now()->subHours(3),
        ], ['QA'], ['Asuransi']);

        $this->createVacancy($mitra->id, [
            'position_name' => 'Accounting Office Senior',
            'job_type' => JobVacancy::JOB_TYPE_FULL_TIME,
            'work_policy' => JobVacancy::WORK_POLICY_OFFICE,
            'experience_level' => JobVacancy::EXPERIENCE_LESS_THAN_ONE_YEAR,
            'province' => 'DKI Jakarta',
            'updated_at' => now()->subDays(10),
        ], ['Akuntansi'], ['BPJS']);

        $query = http_build_query([
            'job_types' => [JobVacancy::JOB_TYPE_FULL_TIME],
            'work_policies' => [JobVacancy::WORK_POLICY_REMOTE],
            'provinces' => ['Jawa Barat'],
            'experience_levels' => [JobVacancy::EXPERIENCE_FRESH_GRADUATE],
            'updated_within' => '24h',
        ]);

        $response = $this->getJson('/api/public/explore/jobs?'.$query);

        $response->assertStatus(200)
            ->assertJsonPath('meta.pagination.total', 1)
            ->assertJsonPath('data.0.position_name', 'QA Remote Fresh Graduate');
    }

    public function test_explore_detail_returns_job_and_recommendations(): void
    {
        $mitraA = $this->createCompanyMitra('PT Detail A', '1234567890114');
        $mitraB = $this->createUmkmMitra('UMKM Detail B', '3201234567899002');

        $main = $this->createVacancy($mitraA->id, [
            'position_name' => 'Backend Developer',
            'province' => 'Jawa Barat',
            'job_type' => JobVacancy::JOB_TYPE_FULL_TIME,
        ], ['Laravel', 'MySQL'], ['Remote Allowance']);

        $this->createVacancy($mitraB->id, [
            'position_name' => 'Frontend Developer',
            'province' => 'Jawa Barat',
            'job_type' => JobVacancy::JOB_TYPE_CONTRACT,
        ], ['Vue'], ['Laptop']);

        $response = $this->getJson('/api/public/explore/jobs/'.$main->slug);

        $response->assertStatus(200)
            ->assertJsonPath('data.job.position_name', 'Backend Developer')
            ->assertJsonPath('data.job.mitra_name', 'PT Detail A')
            ->assertJsonPath('data.job.managed_by', 'PT Detail A')
            ->assertJsonPath('data.recommendations.0.position_name', 'Frontend Developer');

        $response->assertJsonMissingPath('data.job.company_name');
    }

    private function createCompanyMitra(string $companyName, string $nib): User
    {
        $user = User::factory()->create([
            'name' => $companyName,
            'role' => User::ROLE_MITRA,
            'mitra_type' => User::MITRA_PERUSAHAAN,
        ]);

        CompanyProfile::query()->create([
            'user_id' => $user->id,
            'company_name' => $companyName,
            'nib' => $nib,
            'industry_sector' => 'Teknologi',
            'employee_total_range' => '201-500',
            'office_address' => 'Bandung, Jawa Barat',
            'socials' => ['website' => 'https://example.com', 'instagram' => null, 'linkedin' => null, 'whatsapp' => null],
            'short_description' => 'Perusahaan uji untuk endpoint explore.',
            'company_logo_path' => 'profiles/companies/logos/test.png',
            'image_1_path' => null,
            'image_2_path' => null,
            'image_3_path' => null,
            'image_4_path' => null,
            'image_5_path' => null,
            'kemenkumham_decree_path' => 'profiles/companies/legalities/test.pdf',
        ]);

        return $user;
    }

    private function createUmkmMitra(string $businessName, string $ownerNik): User
    {
        $user = User::factory()->create([
            'name' => $businessName,
            'role' => User::ROLE_MITRA,
            'mitra_type' => User::MITRA_UMKM,
        ]);

        UmkmProfile::query()->create([
            'user_id' => $user->id,
            'business_name' => $businessName,
            'owner_nik' => $ownerNik,
            'owner_personal_nib' => 'NIB-UMKM-TEST',
            'business_type' => 'Kuliner',
            'business_address' => 'Semarang, Jawa Tengah',
            'umkm_logo_path' => 'profiles/umkm/logos/test.png',
            'owner_ktp_photo_path' => 'profiles/umkm/ktp/test.png',
            'short_description' => 'UMKM uji untuk endpoint explore.',
            'image_1_path' => 'profiles/umkm/images/1.png',
            'image_2_path' => 'profiles/umkm/images/2.png',
            'image_3_path' => 'profiles/umkm/images/3.png',
            'image_4_path' => 'profiles/umkm/images/4.png',
            'image_5_path' => 'profiles/umkm/images/5.png',
        ]);

        return $user;
    }

    private function createVacancy(int $mitraUserId, array $overrides = [], array $skills = [], array $benefits = []): JobVacancy
    {
        $vacancy = JobVacancy::query()->create(array_merge([
            'mitra_user_id' => $mitraUserId,
            'slug' => 'job-'.str()->random(10),
            'position_name' => 'Posisi Uji',
            'category' => 'Umum',
            'job_type' => JobVacancy::JOB_TYPE_FULL_TIME,
            'work_policy' => JobVacancy::WORK_POLICY_OFFICE,
            'experience_level' => JobVacancy::EXPERIENCE_NO_EXPERIENCE,
            'province' => 'DKI Jakarta',
            'salary_min' => 3000000,
            'salary_max' => 4500000,
            'is_salary_hidden' => false,
            'requirements' => 'Persyaratan uji.',
            'job_description' => 'Deskripsi pekerjaan uji.',
            'is_published' => true,
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ], $overrides));

        foreach ($skills as $skill) {
            $vacancy->skills()->create(['name' => $skill]);
        }

        foreach ($benefits as $benefit) {
            $vacancy->benefits()->create(['name' => $benefit]);
        }

        return $vacancy;
    }
}
