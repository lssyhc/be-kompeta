<?php

namespace Tests\Feature;

use App\Models\StudentAchievement;
use App\Models\StudentApplication;
use App\Models\StudentExperience;
use App\Models\StudentProfile;
use App\Models\StudentSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StudentPortfolioApiTest extends TestCase
{
    use RefreshDatabase;

    // ── CREATE ────────────────────────────────────────────────

    public function test_student_can_create_skill(): void
    {
        Sanctum::actingAs($this->createStudentUser());

        $response = $this->postJson('/api/student/portfolio-items', [
            'type' => 'skill',
            'title' => 'Laravel',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.item.title', 'Laravel');

        $this->assertDatabaseHas('student_skills', ['title' => 'Laravel']);
    }

    public function test_student_can_create_experience(): void
    {
        Sanctum::actingAs($this->createStudentUser());

        $response = $this->postJson('/api/student/portfolio-items', [
            'type' => 'experience',
            'title' => 'Magang',
            'description' => 'Deskripsi magang.',
            'position' => 'Intern',
            'company_name' => 'PT Test',
            'start_date' => '2024-01-01',
            'end_date' => '2024-03-31',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.item.title', 'Magang');

        $this->assertDatabaseHas('student_experiences', ['title' => 'Magang']);
    }

    public function test_student_can_create_achievement(): void
    {
        Sanctum::actingAs($this->createStudentUser());

        $response = $this->postJson('/api/student/portfolio-items', [
            'type' => 'achievement',
            'title' => 'Juara 1',
            'description' => 'Juara 1 LKS.',
            'achievement_date' => '2024-02-10',
            'institution_name' => 'Disdik',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.item.title', 'Juara 1');

        $this->assertDatabaseHas('student_achievements', ['title' => 'Juara 1']);
    }

    public function test_student_can_create_application(): void
    {
        Sanctum::actingAs($this->createStudentUser());

        $response = $this->postJson('/api/student/portfolio-items', [
            'type' => 'application',
            'company_name' => 'PT Maju',
            'role_type' => 'Backend Intern',
            'applied_at' => '2024-03-01',
            'status' => 'submitted',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.item.company_name', 'PT Maju');

        $this->assertDatabaseHas('student_applications', ['company_name' => 'PT Maju']);
    }

    // ── UPDATE ────────────────────────────────────────────────

    public function test_student_can_update_skill(): void
    {
        $student = $this->createStudentUser();
        $profile = $student->studentProfile;
        $skill = StudentSkill::query()->create([
            'student_profile_id' => $profile->id,
            'title' => 'Old Skill',
        ]);

        Sanctum::actingAs($student);

        $response = $this->putJson('/api/student/portfolio-items/'.$skill->id, [
            'type' => 'skill',
            'title' => 'New Skill',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Data portfolio berhasil diperbarui.')
            ->assertJsonPath('data.item.title', 'New Skill');

        $this->assertDatabaseHas('student_skills', ['id' => $skill->id, 'title' => 'New Skill']);
    }

    public function test_student_can_update_experience(): void
    {
        $student = $this->createStudentUser();
        $profile = $student->studentProfile;
        $experience = StudentExperience::query()->create([
            'student_profile_id' => $profile->id,
            'title' => 'Old Title',
            'description' => 'Old desc',
            'position' => 'Intern',
            'company_name' => 'PT Old',
            'start_date' => '2024-01-01',
        ]);

        Sanctum::actingAs($student);

        $response = $this->putJson('/api/student/portfolio-items/'.$experience->id, [
            'type' => 'experience',
            'title' => 'New Title',
            'end_date' => '2024-06-30',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.item.title', 'New Title');

        $this->assertDatabaseHas('student_experiences', [
            'id' => $experience->id,
            'title' => 'New Title',
            'company_name' => 'PT Old', // unchanged
        ]);
    }

    public function test_student_can_update_experience_end_date_to_null(): void
    {
        $student = $this->createStudentUser();
        $profile = $student->studentProfile;
        $experience = StudentExperience::query()->create([
            'student_profile_id' => $profile->id,
            'title' => 'Magang',
            'description' => 'Desc',
            'position' => 'Intern',
            'company_name' => 'PT Test',
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        Sanctum::actingAs($student);

        $response = $this->putJson('/api/student/portfolio-items/'.$experience->id, [
            'type' => 'experience',
            'end_date' => null,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('student_experiences', [
            'id' => $experience->id,
            'end_date' => null,
        ]);
    }

    public function test_student_can_update_achievement(): void
    {
        $student = $this->createStudentUser();
        $profile = $student->studentProfile;
        $achievement = StudentAchievement::query()->create([
            'student_profile_id' => $profile->id,
            'title' => 'Old Achievement',
            'description' => 'Old desc',
            'achievement_date' => '2024-02-10',
            'institution_name' => 'Old Institution',
        ]);

        Sanctum::actingAs($student);

        $response = $this->putJson('/api/student/portfolio-items/'.$achievement->id, [
            'type' => 'achievement',
            'title' => 'New Achievement',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.item.title', 'New Achievement');
    }

    public function test_student_can_update_application(): void
    {
        $student = $this->createStudentUser();
        $profile = $student->studentProfile;
        $application = StudentApplication::query()->create([
            'student_profile_id' => $profile->id,
            'company_name' => 'PT Old',
            'role_type' => 'Intern',
            'applied_at' => now(),
            'status' => 'submitted',
        ]);

        Sanctum::actingAs($student);

        $response = $this->putJson('/api/student/portfolio-items/'.$application->id, [
            'type' => 'application',
            'status' => 'accepted',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.item.status', 'accepted');
    }

    public function test_update_returns_404_for_nonexistent_item(): void
    {
        Sanctum::actingAs($this->createStudentUser());

        $response = $this->putJson('/api/student/portfolio-items/99999', [
            'type' => 'skill',
            'title' => 'Test',
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Item portfolio tidak ditemukan.');
    }

    public function test_update_returns_404_for_other_students_item(): void
    {
        $studentA = $this->createStudentUser('A', '1111111111');
        $studentB = $this->createStudentUser('B', '2222222222');

        $skill = StudentSkill::query()->create([
            'student_profile_id' => $studentA->studentProfile->id,
            'title' => 'Student A Skill',
        ]);

        Sanctum::actingAs($studentB);

        $response = $this->putJson('/api/student/portfolio-items/'.$skill->id, [
            'type' => 'skill',
            'title' => 'Hijacked',
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('student_skills', ['id' => $skill->id, 'title' => 'Student A Skill']);
    }

    public function test_update_returns_422_for_invalid_type(): void
    {
        Sanctum::actingAs($this->createStudentUser());

        $response = $this->putJson('/api/student/portfolio-items/1', [
            'type' => 'invalid',
            'title' => 'Test',
        ]);

        $response->assertStatus(422);
    }

    public function test_non_student_cannot_update_portfolio(): void
    {
        $mitra = User::factory()->create([
            'role' => User::ROLE_MITRA,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        Sanctum::actingAs($mitra);

        $response = $this->putJson('/api/student/portfolio-items/1', [
            'type' => 'skill',
            'title' => 'Test',
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_update_portfolio(): void
    {
        $this->putJson('/api/student/portfolio-items/1', [
            'type' => 'skill',
            'title' => 'Test',
        ])->assertStatus(401);
    }

    // ── DELETE ────────────────────────────────────────────────

    public function test_student_can_delete_skill(): void
    {
        $student = $this->createStudentUser();
        $profile = $student->studentProfile;
        $skill = StudentSkill::query()->create([
            'student_profile_id' => $profile->id,
            'title' => 'To Delete',
        ]);

        Sanctum::actingAs($student);

        $response = $this->deleteJson('/api/student/portfolio-items/'.$skill->id, [
            'type' => 'skill',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Data portfolio berhasil dihapus.');

        $this->assertDatabaseMissing('student_skills', ['id' => $skill->id]);
    }

    public function test_student_can_delete_experience(): void
    {
        $student = $this->createStudentUser();
        $profile = $student->studentProfile;
        $experience = StudentExperience::query()->create([
            'student_profile_id' => $profile->id,
            'title' => 'To Delete',
            'description' => 'Desc',
            'position' => 'Intern',
            'company_name' => 'PT Test',
            'start_date' => '2024-01-01',
        ]);

        Sanctum::actingAs($student);

        $response = $this->deleteJson('/api/student/portfolio-items/'.$experience->id, [
            'type' => 'experience',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('student_experiences', ['id' => $experience->id]);
    }

    public function test_student_can_delete_achievement(): void
    {
        $student = $this->createStudentUser();
        $profile = $student->studentProfile;
        $achievement = StudentAchievement::query()->create([
            'student_profile_id' => $profile->id,
            'title' => 'To Delete',
            'description' => 'Desc',
            'achievement_date' => '2024-02-10',
            'institution_name' => 'Disdik',
        ]);

        Sanctum::actingAs($student);

        $response = $this->deleteJson('/api/student/portfolio-items/'.$achievement->id, [
            'type' => 'achievement',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('student_achievements', ['id' => $achievement->id]);
    }

    public function test_student_can_delete_application(): void
    {
        $student = $this->createStudentUser();
        $profile = $student->studentProfile;
        $application = StudentApplication::query()->create([
            'student_profile_id' => $profile->id,
            'company_name' => 'PT Test',
            'role_type' => 'Intern',
            'applied_at' => now(),
            'status' => 'submitted',
        ]);

        Sanctum::actingAs($student);

        $response = $this->deleteJson('/api/student/portfolio-items/'.$application->id, [
            'type' => 'application',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('student_applications', ['id' => $application->id]);
    }

    public function test_delete_returns_404_for_nonexistent_item(): void
    {
        Sanctum::actingAs($this->createStudentUser());

        $response = $this->deleteJson('/api/student/portfolio-items/99999', [
            'type' => 'skill',
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Item portfolio tidak ditemukan.');
    }

    public function test_delete_returns_404_for_other_students_item(): void
    {
        $studentA = $this->createStudentUser('A', '1111111111');
        $studentB = $this->createStudentUser('B', '2222222222');

        $skill = StudentSkill::query()->create([
            'student_profile_id' => $studentA->studentProfile->id,
            'title' => 'Student A Skill',
        ]);

        Sanctum::actingAs($studentB);

        $response = $this->deleteJson('/api/student/portfolio-items/'.$skill->id, [
            'type' => 'skill',
        ]);

        $response->assertStatus(404);
        $this->assertDatabaseHas('student_skills', ['id' => $skill->id]);
    }

    public function test_delete_returns_422_without_type(): void
    {
        Sanctum::actingAs($this->createStudentUser());

        $response = $this->deleteJson('/api/student/portfolio-items/1', []);

        $response->assertStatus(422);
    }

    public function test_non_student_cannot_delete_portfolio(): void
    {
        $mitra = User::factory()->create([
            'role' => User::ROLE_MITRA,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        Sanctum::actingAs($mitra);

        $response = $this->deleteJson('/api/student/portfolio-items/1', [
            'type' => 'skill',
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_delete_portfolio(): void
    {
        $this->deleteJson('/api/student/portfolio-items/1', [
            'type' => 'skill',
        ])->assertStatus(401);
    }

    // ── HELPERS ───────────────────────────────────────────────

    private function createStudentUser(string $suffix = '', string $nisn = '9999999999'): User
    {
        $user = User::factory()->create([
            'name' => 'Siswa Test '.$suffix,
            'role' => User::ROLE_SISWA,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        StudentProfile::query()->create([
            'user_id' => $user->id,
            'full_name' => 'Siswa Test '.$suffix,
            'nisn' => $nisn,
            'major' => 'Rekayasa Perangkat Lunak',
            'school_origin' => 'SMA Test',
            'graduation_status' => 'active',
            'unique_code' => 'TEST'.$nisn,
        ]);

        $user->load('studentProfile');

        return $user;
    }
}
