<?php

namespace Tests\Feature;

use App\Models\StudentProfile;
use App\Models\StudentSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_can_register_from_public_endpoint(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $response = $this->post('/api/auth/register', [
            'role' => User::ROLE_SEKOLAH,
            'email' => 'smk@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'school_name' => 'SMK Negeri 1',
            'npsn' => '12345678',
            'accreditation' => 'A',
            'address' => 'Jl. Pendidikan No. 1',
            'expertise_fields' => ['RPL', 'TKJ'],
            'logo' => UploadedFile::fake()->image('logo.jpg'),
            'short_description' => 'SMK unggulan.',
            'operational_license' => UploadedFile::fake()->create('izin.pdf', 100, 'application/pdf'),
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.role', User::ROLE_SEKOLAH)
            ->assertJsonPath('data.user.account_status', 'active');

        $this->assertDatabaseHas('users', [
            'email' => 'smk@example.com',
            'role' => User::ROLE_SEKOLAH,
        ]);

        $this->assertDatabaseHas('school_profiles', [
            'npsn' => '12345678',
            'school_name' => 'SMK Negeri 1',
        ]);
    }

    public function test_mitra_perusahaan_register_rejects_forbidden_fields(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $response = $this->post('/api/auth/register', [
            'role' => User::ROLE_MITRA,
            'mitra_type' => User::MITRA_PERUSAHAAN,
            'email' => 'corp@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_name' => 'PT Maju Jaya',
            'nib' => '1234567890123',
            'industry_sector' => 'IT',
            'employee_total_range' => '51-200',
            'office_address' => 'Jl. Sudirman No. 10',
            'short_description' => 'Perusahaan teknologi fokus pada software enterprise.',
            'company_logo' => UploadedFile::fake()->image('company-logo.jpg'),
            'kemenkumham_decree' => UploadedFile::fake()->create('sk.pdf', 100, 'application/pdf'),
            'pinpoint_map' => '-6.2,106.8',
            'contact_person' => '08123456789',
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['pinpoint_map', 'contact_person']);
    }

    public function test_umkm_register_requires_all_five_images(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $response = $this->post('/api/auth/register', [
            'role' => User::ROLE_MITRA,
            'mitra_type' => User::MITRA_UMKM,
            'email' => 'umkm@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'business_name' => 'UMKM Test',
            'owner_nik' => '3201234567891111',
            'business_type' => 'Kuliner',
            'business_address' => 'Jl. UMKM No. 1',
            'short_description' => 'UMKM makanan rumahan.',
            'umkm_logo' => UploadedFile::fake()->image('umkm-logo.jpg'),
            'owner_ktp_photo' => UploadedFile::fake()->image('ktp.jpg'),
            'image_1' => UploadedFile::fake()->image('img1.jpg'),
            'image_2' => UploadedFile::fake()->image('img2.jpg'),
            'image_3' => UploadedFile::fake()->image('img3.jpg'),
            'image_4' => UploadedFile::fake()->image('img4.jpg'),
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image_5']);
    }

    public function test_umkm_register_requires_logo(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $response = $this->post('/api/auth/register', [
            'role' => User::ROLE_MITRA,
            'mitra_type' => User::MITRA_UMKM,
            'email' => 'umkm-logo@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'business_name' => 'UMKM Logo Test',
            'owner_nik' => '3201234567892222',
            'business_type' => 'Fashion',
            'business_address' => 'Jl. Logo No. 2',
            'short_description' => 'UMKM yang butuh logo wajib.',
            'owner_ktp_photo' => UploadedFile::fake()->image('ktp.jpg'),
            'image_1' => UploadedFile::fake()->image('img1.jpg'),
            'image_2' => UploadedFile::fake()->image('img2.jpg'),
            'image_3' => UploadedFile::fake()->image('img3.jpg'),
            'image_4' => UploadedFile::fake()->image('img4.jpg'),
            'image_5' => UploadedFile::fake()->image('img5.jpg'),
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['umkm_logo']);
    }

    public function test_admin_cannot_register_from_public_endpoint(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'role' => User::ROLE_ADMIN,
            'email' => 'admin2@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    public function test_student_cannot_register_from_public_endpoint(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'role' => User::ROLE_SISWA,
            'email' => 'siswa@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    public function test_school_can_add_student_and_system_generates_unique_code(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $school = User::factory()->create([
            'role' => User::ROLE_SEKOLAH,
            'account_status' => 'active',
            'is_active' => true,
        ]);

        Sanctum::actingAs($school);

        $response = $this->post('/api/school/students', [
            'full_name' => 'Budi Santoso',
            'nisn' => '1234567890',
            'photo_profile' => UploadedFile::fake()->image('student.jpg'),
            'major' => 'RPL',
            'school_origin' => 'SMK Negeri 1',
            'graduation_status' => 'Belum Lulus',
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.role', User::ROLE_SISWA);

        $this->assertDatabaseHas('student_profiles', [
            'nisn' => '1234567890',
            'major' => 'RPL',
            'school_origin' => 'SMK Negeri 1',
        ]);

        $studentProfile = StudentProfile::query()->where('nisn', '1234567890')->first();

        $this->assertNotNull($studentProfile);
        $this->assertNotEmpty($studentProfile->unique_code);
    }

    public function test_school_can_hard_delete_its_student(): void
    {
        $school = User::factory()->create([
            'role' => User::ROLE_SEKOLAH,
            'account_status' => 'active',
            'is_active' => true,
        ]);

        $studentUser = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'account_status' => 'active',
            'is_active' => true,
        ]);

        $studentProfile = StudentProfile::query()->create([
            'user_id' => $studentUser->id,
            'school_user_id' => $school->id,
            'full_name' => 'Siswa Hapus',
            'nisn' => '1111111111',
            'major' => 'RPL',
            'school_origin' => 'SMK Negeri 1',
            'graduation_status' => 'Belum Lulus',
            'unique_code' => 'DEL12345',
        ]);

        Sanctum::actingAs($school);

        $response = $this->deleteJson('/api/school/students/'.$studentProfile->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Data siswa berhasil dihapus.');

        $this->assertDatabaseMissing('student_profiles', [
            'id' => $studentProfile->id,
        ]);

        $this->assertDatabaseMissing('users', [
            'id' => $studentUser->id,
        ]);
    }

    public function test_school_delete_student_returns_not_found_when_not_owned(): void
    {
        $school = User::factory()->create([
            'role' => User::ROLE_SEKOLAH,
            'account_status' => 'active',
            'is_active' => true,
        ]);

        $otherSchool = User::factory()->create([
            'role' => User::ROLE_SEKOLAH,
            'account_status' => 'active',
            'is_active' => true,
        ]);

        $studentUser = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'account_status' => 'active',
            'is_active' => true,
        ]);

        $studentProfile = StudentProfile::query()->create([
            'user_id' => $studentUser->id,
            'school_user_id' => $otherSchool->id,
            'full_name' => 'Siswa Sekolah Lain',
            'nisn' => '2222222222',
            'major' => 'TKJ',
            'school_origin' => 'SMK Negeri 2',
            'graduation_status' => 'Belum Lulus',
            'unique_code' => 'OTH12345',
        ]);

        Sanctum::actingAs($school);

        $response = $this->deleteJson('/api/school/students/'.$studentProfile->id);

        $response->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Data siswa tidak ditemukan.');
    }

    public function test_non_school_user_cannot_delete_student(): void
    {
        $mitra = User::factory()->create([
            'role' => User::ROLE_MITRA,
            'account_status' => 'active',
            'is_active' => true,
        ]);

        Sanctum::actingAs($mitra);

        $response = $this->deleteJson('/api/school/students/9999');

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Hanya user sekolah yang dapat menghapus data siswa.');
    }

    public function test_student_can_login_with_nisn_school_origin_and_unique_code(): void
    {
        $studentUser = User::factory()->create([
            'email' => null,
            'role' => User::ROLE_SISWA,
            'account_status' => 'active',
            'is_active' => true,
        ]);

        StudentProfile::query()->create([
            'user_id' => $studentUser->id,
            'full_name' => 'Andi Pratama',
            'nisn' => '1234567890',
            'major' => 'TKJ',
            'school_origin' => 'SMK Negeri 1',
            'graduation_status' => 'Lulus',
            'unique_code' => 'ABC123XY',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'role' => User::ROLE_SISWA,
            'nisn' => '1234567890',
            'school_origin' => 'SMK Negeri 1',
            'unique_code' => 'ABC123XY',
            'device_name' => 'phpunit',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['token', 'token_type', 'user', 'requires_skill_setup', 'next_step'],
                'errors',
                'meta',
            ])
            ->assertJsonPath('data.requires_skill_setup', true)
            ->assertJsonPath('data.next_step', 'add_skill');

        $this->assertNotNull($studentUser->fresh()->last_login_at);
    }

    public function test_student_login_returns_no_skill_setup_when_skill_exists(): void
    {
        $studentUser = User::factory()->create([
            'email' => null,
            'role' => User::ROLE_SISWA,
            'account_status' => 'active',
            'is_active' => true,
        ]);

        $studentProfile = StudentProfile::query()->create([
            'user_id' => $studentUser->id,
            'full_name' => 'Nina Maharani',
            'nisn' => '5556667778',
            'major' => 'RPL',
            'school_origin' => 'SMK Negeri 2',
            'graduation_status' => 'Belum Lulus',
            'unique_code' => 'ZXCV1234',
        ]);

        StudentSkill::query()->create([
            'student_profile_id' => $studentProfile->id,
            'title' => 'Laravel',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'role' => User::ROLE_SISWA,
            'nisn' => '5556667778',
            'school_origin' => 'SMK Negeri 2',
            'unique_code' => 'ZXCV1234',
            'device_name' => 'phpunit',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.requires_skill_setup', false)
            ->assertJsonPath('data.next_step', null);
    }

    public function test_school_can_login_with_email_and_password(): void
    {
        $school = User::factory()->create([
            'email' => 'school@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_SEKOLAH,
            'account_status' => 'active',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'role' => User::ROLE_SEKOLAH,
            'email' => 'school@example.com',
            'password' => 'password123',
            'device_name' => 'phpunit',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['token', 'token_type', 'user', 'requires_skill_setup', 'next_step'],
                'errors',
                'meta',
            ]);

        $this->assertNotNull($school->fresh()->last_login_at);
    }
}
