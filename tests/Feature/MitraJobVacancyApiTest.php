<?php

namespace Tests\Feature;

use App\Models\CompanyProfile;
use App\Models\JobVacancy;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MitraJobVacancyApiTest extends TestCase
{
    use RefreshDatabase;

    // ─── INDEX ──────────────────────────────────────────

    public function test_mitra_can_list_own_vacancies(): void
    {
        $mitra = $this->createCompanyMitra('PT Lowongan Test', '9999900001111');
        $this->createVacancy($mitra->id, ['position_name' => 'Dev A']);
        $this->createVacancy($mitra->id, ['position_name' => 'Dev B', 'is_published' => false]);

        $response = $this->actingAs($mitra)->getJson('/api/mitra/job-vacancies');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.pagination.total', 2);
    }

    public function test_mitra_can_filter_vacancies_by_published_status(): void
    {
        $mitra = $this->createCompanyMitra('PT Filter Test', '9999900001112');
        $this->createVacancy($mitra->id, ['position_name' => 'Published Job', 'is_published' => true]);
        $this->createVacancy($mitra->id, ['position_name' => 'Draft Job', 'is_published' => false]);

        $published = $this->actingAs($mitra)->getJson('/api/mitra/job-vacancies?is_published=1');
        $published->assertStatus(200)
            ->assertJsonPath('meta.pagination.total', 1)
            ->assertJsonPath('data.0.position_name', 'Published Job');

        $draft = $this->actingAs($mitra)->getJson('/api/mitra/job-vacancies?is_published=0');
        $draft->assertStatus(200)
            ->assertJsonPath('meta.pagination.total', 1)
            ->assertJsonPath('data.0.position_name', 'Draft Job');
    }

    public function test_mitra_only_sees_own_vacancies(): void
    {
        $mitraA = $this->createCompanyMitra('PT A', '9999900001113');
        $mitraB = $this->createUmkmMitra('UMKM B', '3201234567899991');

        $this->createVacancy($mitraA->id, ['position_name' => 'Job milik A']);
        $this->createVacancy($mitraB->id, ['position_name' => 'Job milik B']);

        $response = $this->actingAs($mitraA)->getJson('/api/mitra/job-vacancies');

        $response->assertStatus(200)
            ->assertJsonPath('meta.pagination.total', 1)
            ->assertJsonPath('data.0.position_name', 'Job milik A');
    }

    // ─── STORE ──────────────────────────────────────────

    public function test_mitra_can_create_vacancy_with_skills_and_benefits(): void
    {
        $mitra = $this->createCompanyMitra('PT Create Test', '9999900001114');

        $payload = [
            'position_name' => 'Frontend Developer',
            'category' => 'Teknologi',
            'job_type' => 'penuh_waktu',
            'work_policy' => 'remote',
            'experience_level' => 'fresh_graduate',
            'province' => 'DKI Jakarta',
            'salary_min' => 6000000,
            'salary_max' => 8000000,
            'is_salary_hidden' => false,
            'requirements' => "Menguasai HTML, CSS, JS.\nPengalaman React menjadi nilai plus.",
            'job_description' => 'Mengembangkan tampilan frontend aplikasi web.',
            'is_published' => true,
            'skills' => ['HTML', 'CSS', 'JavaScript', 'React'],
            'benefits' => ['Remote Allowance', 'BPJS Kesehatan'],
        ];

        $response = $this->actingAs($mitra)->postJson('/api/mitra/job-vacancies', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Lowongan berhasil dibuat.')
            ->assertJsonPath('data.position_name', 'Frontend Developer')
            ->assertJsonPath('data.category', 'Teknologi')
            ->assertJsonPath('data.job_type', 'penuh_waktu')
            ->assertJsonPath('data.work_policy', 'remote')
            ->assertJsonPath('data.is_published', true)
            ->assertJsonPath('data.skills', ['HTML', 'CSS', 'JavaScript', 'React'])
            ->assertJsonPath('data.benefits', ['Remote Allowance', 'BPJS Kesehatan']);

        $this->assertNotNull($response->json('data.slug'));
        $this->assertDatabaseHas('job_vacancies', ['position_name' => 'Frontend Developer', 'mitra_user_id' => $mitra->id]);
    }

    public function test_mitra_can_create_draft_vacancy(): void
    {
        $mitra = $this->createCompanyMitra('PT Draft Test', '9999900001115');

        $payload = [
            'position_name' => 'Draft Position',
            'category' => 'Umum',
            'job_type' => 'kontrak',
            'work_policy' => 'kantor',
            'experience_level' => 'tidak_berpengalaman',
            'province' => 'Jawa Barat',
            'job_description' => 'Deskripsi draft.',
            'is_published' => false,
        ];

        $response = $this->actingAs($mitra)->postJson('/api/mitra/job-vacancies', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.is_published', false);
    }

    public function test_store_validates_required_fields(): void
    {
        $mitra = $this->createCompanyMitra('PT Validation Test', '9999900001116');

        $response = $this->actingAs($mitra)->postJson('/api/mitra/job-vacancies', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['position_name', 'category', 'job_type', 'work_policy', 'experience_level', 'province', 'job_description']);
    }

    public function test_store_validates_enum_values(): void
    {
        $mitra = $this->createCompanyMitra('PT Enum Test', '9999900001117');

        $payload = [
            'position_name' => 'Test',
            'category' => 'Test',
            'job_type' => 'invalid_type',
            'work_policy' => 'invalid_policy',
            'experience_level' => 'invalid_level',
            'province' => 'Planet Mars',
            'job_description' => 'Test.',
        ];

        $response = $this->actingAs($mitra)->postJson('/api/mitra/job-vacancies', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['job_type', 'work_policy', 'experience_level', 'province']);
    }

    public function test_store_validates_salary_max_gte_salary_min(): void
    {
        $mitra = $this->createCompanyMitra('PT Salary Test', '9999900001118');

        $payload = [
            'position_name' => 'Test',
            'category' => 'Test',
            'job_type' => 'penuh_waktu',
            'work_policy' => 'kantor',
            'experience_level' => 'fresh_graduate',
            'province' => 'DKI Jakarta',
            'job_description' => 'Test.',
            'salary_min' => 10000000,
            'salary_max' => 5000000,
        ];

        $response = $this->actingAs($mitra)->postJson('/api/mitra/job-vacancies', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['salary_max']);
    }

    // ─── SHOW ───────────────────────────────────────────

    public function test_mitra_can_view_own_vacancy_detail(): void
    {
        $mitra = $this->createCompanyMitra('PT Show Test', '9999900001119');
        $vacancy = $this->createVacancy($mitra->id, [
            'position_name' => 'Detail Position',
        ], ['Laravel', 'PHP'], ['BPJS']);

        $response = $this->actingAs($mitra)->getJson('/api/mitra/job-vacancies/'.$vacancy->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.position_name', 'Detail Position')
            ->assertJsonPath('data.skills', ['Laravel', 'PHP'])
            ->assertJsonPath('data.benefits', ['BPJS']);
    }

    public function test_mitra_cannot_view_other_mitra_vacancy(): void
    {
        $mitraA = $this->createCompanyMitra('PT Owner', '9999900001120');
        $mitraB = $this->createUmkmMitra('UMKM Other', '3201234567899992');

        $vacancy = $this->createVacancy($mitraA->id);

        $response = $this->actingAs($mitraB)->getJson('/api/mitra/job-vacancies/'.$vacancy->id);

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Lowongan tidak ditemukan.');
    }

    public function test_show_returns_404_for_nonexistent_vacancy(): void
    {
        $mitra = $this->createCompanyMitra('PT 404 Test', '9999900001121');

        $response = $this->actingAs($mitra)->getJson('/api/mitra/job-vacancies/99999');

        $response->assertStatus(404);
    }

    // ─── UPDATE ─────────────────────────────────────────

    public function test_mitra_can_update_vacancy_partially(): void
    {
        $mitra = $this->createCompanyMitra('PT Update Test', '9999900001122');
        $vacancy = $this->createVacancy($mitra->id, [
            'position_name' => 'Original Position',
            'salary_min' => 5000000,
            'salary_max' => 6000000,
        ]);

        $response = $this->actingAs($mitra)->putJson('/api/mitra/job-vacancies/'.$vacancy->id, [
            'position_name' => 'Updated Position',
            'salary_min' => 7000000,
            'salary_max' => 9000000,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.position_name', 'Updated Position')
            ->assertJsonPath('data.salary_min', 7000000)
            ->assertJsonPath('data.salary_max', 9000000);
    }

    public function test_mitra_can_update_skills_and_benefits(): void
    {
        $mitra = $this->createCompanyMitra('PT Sync Test', '9999900001123');
        $vacancy = $this->createVacancy($mitra->id, [], ['OldSkill'], ['OldBenefit']);

        $response = $this->actingAs($mitra)->putJson('/api/mitra/job-vacancies/'.$vacancy->id, [
            'skills' => ['NewSkillA', 'NewSkillB'],
            'benefits' => ['NewBenefit'],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.skills', ['NewSkillA', 'NewSkillB'])
            ->assertJsonPath('data.benefits', ['NewBenefit']);

        $this->assertDatabaseMissing('job_vacancy_skills', ['name' => 'OldSkill']);
        $this->assertDatabaseMissing('job_vacancy_benefits', ['name' => 'OldBenefit']);
    }

    public function test_mitra_can_publish_draft(): void
    {
        $mitra = $this->createCompanyMitra('PT Publish Test', '9999900001124');
        $vacancy = $this->createVacancy($mitra->id, ['is_published' => false]);

        $response = $this->actingAs($mitra)->putJson('/api/mitra/job-vacancies/'.$vacancy->id, [
            'is_published' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_published', true);
    }

    public function test_mitra_cannot_update_other_mitra_vacancy(): void
    {
        $mitraA = $this->createCompanyMitra('PT Owner Update', '9999900001125');
        $mitraB = $this->createUmkmMitra('UMKM Intruder', '3201234567899993');

        $vacancy = $this->createVacancy($mitraA->id);

        $response = $this->actingAs($mitraB)->putJson('/api/mitra/job-vacancies/'.$vacancy->id, [
            'position_name' => 'Hacked',
        ]);

        $response->assertStatus(404);
    }

    // ─── DESTROY ────────────────────────────────────────

    public function test_mitra_can_delete_own_vacancy(): void
    {
        $mitra = $this->createCompanyMitra('PT Delete Test', '9999900001126');
        $vacancy = $this->createVacancy($mitra->id, [], ['Skill'], ['Benefit']);

        $response = $this->actingAs($mitra)->deleteJson('/api/mitra/job-vacancies/'.$vacancy->id);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Lowongan berhasil dihapus.');

        $this->assertDatabaseMissing('job_vacancies', ['id' => $vacancy->id]);
        $this->assertDatabaseMissing('job_vacancy_skills', ['job_vacancy_id' => $vacancy->id]);
        $this->assertDatabaseMissing('job_vacancy_benefits', ['job_vacancy_id' => $vacancy->id]);
    }

    public function test_mitra_cannot_delete_other_mitra_vacancy(): void
    {
        $mitraA = $this->createCompanyMitra('PT Owner Delete', '9999900001127');
        $mitraB = $this->createUmkmMitra('UMKM Intruder Delete', '3201234567899994');

        $vacancy = $this->createVacancy($mitraA->id);

        $response = $this->actingAs($mitraB)->deleteJson('/api/mitra/job-vacancies/'.$vacancy->id);

        $response->assertStatus(404);
        $this->assertDatabaseHas('job_vacancies', ['id' => $vacancy->id]);
    }

    // ─── AUTHORIZATION ──────────────────────────────────

    public function test_non_mitra_gets_403_on_all_endpoints(): void
    {
        $siswa = User::factory()->create([
            'role' => User::ROLE_SISWA,
        ]);

        $this->actingAs($siswa)->getJson('/api/mitra/job-vacancies')->assertStatus(403);
        $this->actingAs($siswa)->postJson('/api/mitra/job-vacancies', [])->assertStatus(403);
        $this->actingAs($siswa)->getJson('/api/mitra/job-vacancies/1')->assertStatus(403);
        $this->actingAs($siswa)->putJson('/api/mitra/job-vacancies/1', [])->assertStatus(403);
        $this->actingAs($siswa)->deleteJson('/api/mitra/job-vacancies/1')->assertStatus(403);
    }

    public function test_unauthenticated_gets_401(): void
    {
        $this->getJson('/api/mitra/job-vacancies')->assertStatus(401);
        $this->postJson('/api/mitra/job-vacancies', [])->assertStatus(401);
        $this->getJson('/api/mitra/job-vacancies/1')->assertStatus(401);
        $this->putJson('/api/mitra/job-vacancies/1', [])->assertStatus(401);
        $this->deleteJson('/api/mitra/job-vacancies/1')->assertStatus(401);
    }

    // ─── UMKM MITRA ────────────────────────────────────

    public function test_umkm_mitra_can_create_and_manage_vacancies(): void
    {
        $mitra = $this->createUmkmMitra('UMKM CRUD Test', '3201234567899995');

        $payload = [
            'position_name' => 'Kasir Part-Time',
            'category' => 'Penjualan',
            'job_type' => 'paruh_waktu',
            'work_policy' => 'kantor',
            'experience_level' => 'tidak_berpengalaman',
            'province' => 'Jawa Tengah',
            'job_description' => 'Melayani transaksi pembayaran pelanggan.',
            'is_published' => true,
            'skills' => ['Kasir'],
            'benefits' => ['Makan Siang'],
        ];

        $createResponse = $this->actingAs($mitra)->postJson('/api/mitra/job-vacancies', $payload);
        $createResponse->assertStatus(201);

        $vacancyId = $createResponse->json('data.id');

        $this->actingAs($mitra)->getJson('/api/mitra/job-vacancies/'.$vacancyId)->assertStatus(200);
        $this->actingAs($mitra)->putJson('/api/mitra/job-vacancies/'.$vacancyId, ['position_name' => 'Senior Kasir'])->assertStatus(200);
        $this->actingAs($mitra)->deleteJson('/api/mitra/job-vacancies/'.$vacancyId)->assertStatus(200);
    }

    // ─── SLUG GENERATION ────────────────────────────────

    public function test_slug_is_auto_generated_and_unique(): void
    {
        $mitra = $this->createCompanyMitra('PT Slug Test', '9999900001128');

        $payload = [
            'position_name' => 'Backend Developer',
            'category' => 'Teknologi',
            'job_type' => 'penuh_waktu',
            'work_policy' => 'kantor',
            'experience_level' => 'fresh_graduate',
            'province' => 'DKI Jakarta',
            'job_description' => 'Deskripsi job 1.',
        ];

        $resp1 = $this->actingAs($mitra)->postJson('/api/mitra/job-vacancies', $payload);
        $resp2 = $this->actingAs($mitra)->postJson('/api/mitra/job-vacancies', array_merge($payload, ['job_description' => 'Deskripsi job 2.']));

        $resp1->assertStatus(201);
        $resp2->assertStatus(201);

        $slug1 = $resp1->json('data.slug');
        $slug2 = $resp2->json('data.slug');

        $this->assertNotEquals($slug1, $slug2);
        $this->assertStringStartsWith('backend-developer-', $slug1);
        $this->assertStringStartsWith('backend-developer-', $slug2);
    }

    // ─── HELPERS ────────────────────────────────────────

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
            'office_address' => 'Jakarta, DKI Jakarta',
            'socials' => ['website' => null, 'instagram' => null, 'linkedin' => null, 'whatsapp' => null],
            'short_description' => 'Perusahaan uji.',
            'company_logo_path' => 'profiles/companies/logos/test.png',
            'image_1_path' => '',
            'image_2_path' => '',
            'image_3_path' => '',
            'image_4_path' => '',
            'image_5_path' => '',
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
            'short_description' => 'UMKM uji.',
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
