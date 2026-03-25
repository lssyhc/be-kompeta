<?php

namespace Tests\Feature;

use App\Models\CompanyProfile;
use App\Models\JobVacancy;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StudentBookmarkApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_list_empty_bookmarks(): void
    {
        $student = $this->createStudentUser();
        Sanctum::actingAs($student, ['siswa']);

        $response = $this->getJson('/api/student/bookmarks');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', [])
            ->assertJsonPath('meta.pagination.total', 0);
    }

    public function test_student_can_add_bookmark(): void
    {
        $student = $this->createStudentUser();
        $mitra = $this->createCompanyMitra();
        $job = $this->createVacancy($mitra->id);

        Sanctum::actingAs($student, ['siswa']);

        $response = $this->postJson('/api/student/bookmarks/'.$job->id);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Lowongan berhasil ditambahkan ke bookmark.');

        $this->assertDatabaseHas('job_vacancy_bookmarks', [
            'user_id' => $student->id,
            'job_vacancy_id' => $job->id,
        ]);
    }

    public function test_student_cannot_bookmark_same_job_twice(): void
    {
        $student = $this->createStudentUser();
        $mitra = $this->createCompanyMitra();
        $job = $this->createVacancy($mitra->id);

        Sanctum::actingAs($student, ['siswa']);

        $this->postJson('/api/student/bookmarks/'.$job->id)->assertStatus(201);
        $response = $this->postJson('/api/student/bookmarks/'.$job->id);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Lowongan sudah ada di bookmark.');
    }

    public function test_student_cannot_bookmark_unpublished_job(): void
    {
        $student = $this->createStudentUser();
        $mitra = $this->createCompanyMitra();
        $job = $this->createVacancy($mitra->id, ['is_published' => false]);

        Sanctum::actingAs($student, ['siswa']);

        $response = $this->postJson('/api/student/bookmarks/'.$job->id);

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_student_cannot_bookmark_nonexistent_job(): void
    {
        $student = $this->createStudentUser();
        Sanctum::actingAs($student, ['siswa']);

        $response = $this->postJson('/api/student/bookmarks/999999');

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_student_can_remove_bookmark(): void
    {
        $student = $this->createStudentUser();
        $mitra = $this->createCompanyMitra();
        $job = $this->createVacancy($mitra->id);

        Sanctum::actingAs($student, ['siswa']);

        $this->postJson('/api/student/bookmarks/'.$job->id)->assertStatus(201);

        $response = $this->deleteJson('/api/student/bookmarks/'.$job->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Bookmark berhasil dihapus.');

        $this->assertDatabaseMissing('job_vacancy_bookmarks', [
            'user_id' => $student->id,
            'job_vacancy_id' => $job->id,
        ]);
    }

    public function test_student_cannot_remove_nonexistent_bookmark(): void
    {
        $student = $this->createStudentUser();
        Sanctum::actingAs($student, ['siswa']);

        $response = $this->deleteJson('/api/student/bookmarks/999999');

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_student_can_list_bookmarks_with_job_details(): void
    {
        $student = $this->createStudentUser();
        $mitra = $this->createCompanyMitra();
        $job = $this->createVacancy($mitra->id, [
            'position_name' => 'Backend Developer',
            'province' => 'Jawa Barat',
        ], ['Laravel', 'MySQL'], ['BPJS', 'THR']);

        Sanctum::actingAs($student, ['siswa']);

        $this->postJson('/api/student/bookmarks/'.$job->id)->assertStatus(201);

        $response = $this->getJson('/api/student/bookmarks');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.pagination.total', 1)
            ->assertJsonPath('data.0.position_name', 'Backend Developer')
            ->assertJsonPath('data.0.province', 'Jawa Barat')
            ->assertJsonPath('data.0.skills', ['Laravel', 'MySQL'])
            ->assertJsonPath('data.0.benefits', ['BPJS', 'THR']);

        $this->assertNotNull($response->json('data.0.created_at_human'));
    }

    public function test_non_student_cannot_access_bookmarks(): void
    {
        $mitra = $this->createCompanyMitra();
        Sanctum::actingAs($mitra, ['mitra']);

        $this->getJson('/api/student/bookmarks')->assertStatus(403);
        $this->postJson('/api/student/bookmarks/1')->assertStatus(403);
        $this->deleteJson('/api/student/bookmarks/1')->assertStatus(403);
    }

    public function test_unauthenticated_cannot_access_bookmarks(): void
    {
        $this->getJson('/api/student/bookmarks')->assertStatus(401);
        $this->postJson('/api/student/bookmarks/1')->assertStatus(401);
        $this->deleteJson('/api/student/bookmarks/1')->assertStatus(401);
    }

    public function test_bookmarks_pagination_works(): void
    {
        $student = $this->createStudentUser();
        $mitra = $this->createCompanyMitra();

        Sanctum::actingAs($student, ['siswa']);

        for ($i = 0; $i < 3; $i++) {
            $job = $this->createVacancy($mitra->id, ['position_name' => "Job {$i}"]);
            $this->postJson('/api/student/bookmarks/'.$job->id)->assertStatus(201);
        }

        $response = $this->getJson('/api/student/bookmarks?per_page=2');

        $response->assertStatus(200)
            ->assertJsonPath('meta.pagination.total', 3)
            ->assertJsonPath('meta.pagination.per_page', 2)
            ->assertJsonPath('meta.pagination.has_more_pages', true);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_unpublished_bookmarked_jobs_not_shown_in_list(): void
    {
        $student = $this->createStudentUser();
        $mitra = $this->createCompanyMitra();
        $job = $this->createVacancy($mitra->id);

        Sanctum::actingAs($student, ['siswa']);

        $this->postJson('/api/student/bookmarks/'.$job->id)->assertStatus(201);

        // Unpublish the job after bookmarking
        $job->update(['is_published' => false]);

        $response = $this->getJson('/api/student/bookmarks');

        $response->assertStatus(200)
            ->assertJsonPath('meta.pagination.total', 0);
    }

    private function createStudentUser(): User
    {
        $user = User::factory()->create([
            'name' => 'Siswa Test',
            'role' => User::ROLE_SISWA,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        StudentProfile::query()->create([
            'user_id' => $user->id,
            'full_name' => 'Siswa Test',
            'nisn' => '9999999999',
            'major' => 'Rekayasa Perangkat Lunak',
            'school_origin' => 'SMA Test',
            'graduation_status' => 'active',
            'unique_code' => 'TEST999999',
        ]);

        return $user;
    }

    private function createCompanyMitra(): User
    {
        $user = User::factory()->create([
            'name' => 'PT Test Mitra',
            'role' => User::ROLE_MITRA,
            'mitra_type' => User::MITRA_PERUSAHAAN,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        CompanyProfile::query()->create([
            'user_id' => $user->id,
            'company_name' => 'PT Test Mitra',
            'nib' => '9999999999999',
            'industry_sector' => 'Teknologi',
            'employee_total_range' => '1-50',
            'office_address' => 'Jakarta',
            'socials' => ['website' => null, 'instagram' => null, 'linkedin' => null, 'whatsapp' => null],
            'short_description' => 'Test company.',
            'company_logo_path' => 'profiles/companies/logos/test.png',
            'image_1_path' => 'profiles/companies/images/1.png',
            'image_2_path' => 'profiles/companies/images/2.png',
            'image_3_path' => 'profiles/companies/images/3.png',
            'image_4_path' => 'profiles/companies/images/4.png',
            'image_5_path' => 'profiles/companies/images/5.png',
            'kemenkumham_decree_path' => 'profiles/companies/legalities/test.pdf',
        ]);

        return $user;
    }

    private function createVacancy(int $mitraUserId, array $overrides = [], array $skills = [], array $benefits = []): JobVacancy
    {
        $vacancy = JobVacancy::query()->create(array_merge([
            'mitra_user_id' => $mitraUserId,
            'slug' => 'test-job-'.str()->random(10),
            'position_name' => 'Test Position',
            'category' => 'Umum',
            'job_type' => JobVacancy::JOB_TYPE_FULL_TIME,
            'work_policy' => JobVacancy::WORK_POLICY_OFFICE,
            'experience_level' => JobVacancy::EXPERIENCE_NO_EXPERIENCE,
            'province' => 'DKI Jakarta',
            'salary_min' => 3000000,
            'salary_max' => 4500000,
            'is_salary_hidden' => false,
            'requirements' => 'Test requirements.',
            'job_description' => 'Test description.',
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
