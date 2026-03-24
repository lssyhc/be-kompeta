<?php

namespace Tests\Feature;

use App\Models\AdminProfile;
use App\Models\CompanyProfile;
use App\Models\SchoolProfile;
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
            ->assertJsonPath('data.user.account_status', User::STATUS_PENDING)
            ->assertJsonPath('data.user.is_active', false)
            ->assertJsonPath('data.role_profile.npsn', '12345678');

        $this->assertDatabaseHas('users', [
            'email' => 'smk@example.com',
            'role' => User::ROLE_SEKOLAH,
        ]);

        $this->assertDatabaseHas('school_profiles', [
            'npsn' => '12345678',
            'school_name' => 'SMK Negeri 1',
        ]);
    }

    public function test_pending_school_cannot_login_before_admin_approval(): void
    {
        $school = User::factory()->create([
            'email' => 'pending-school@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_SEKOLAH,
            'account_status' => User::STATUS_PENDING,
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'role' => User::ROLE_SEKOLAH,
            'email' => 'pending-school@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Akun belum disetujui. Mohon menunggu persetujuan dari Tim Kompeta untuk pengaktifan akun. Pemberitahuan pengaktifan akan dikirimkan melalui email Anda nanti.');

        $this->assertNull($school->fresh()->last_login_at);
    }

    public function test_admin_can_approve_pending_school_and_get_summary_payload(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'account_status' => User::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        $school = User::factory()->create([
            'name' => 'SMK Approval 1',
            'email' => 'approval-school@example.com',
            'role' => User::ROLE_SEKOLAH,
            'account_status' => User::STATUS_PENDING,
            'is_active' => false,
        ]);

        SchoolProfile::query()->create([
            'user_id' => $school->id,
            'school_name' => 'SMK Approval 1',
            'npsn' => '90909090',
            'accreditation' => 'A',
            'address' => 'Jl. Approval No. 1',
            'expertise_fields' => ['RPL'],
            'logo_path' => 'profiles/schools/logos/logo.jpg',
            'short_description' => 'Sekolah menunggu approval admin.',
            'operational_license_path' => 'profiles/schools/legalities/izin.pdf',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson('/api/admin/registrations/'.$school->id.'/approve');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $school->id)
            ->assertJsonPath('data.nama', 'SMK Approval 1')
            ->assertJsonPath('data.title', 'Sekolah')
            ->assertJsonPath('data.status', User::STATUS_ACTIVE)
            ->assertJsonPath('data.detail.school_name', 'SMK Approval 1')
            ->assertJsonPath('data.detail.npsn', '90909090');

        $this->assertDatabaseHas('users', [
            'id' => $school->id,
            'account_status' => User::STATUS_ACTIVE,
            'is_active' => true,
        ]);
    }

    public function test_public_mitra_and_school_endpoints_only_show_approved_accounts(): void
    {
        $approvedSchool = User::factory()->create([
            'name' => 'SMK Approved',
            'role' => User::ROLE_SEKOLAH,
            'account_status' => User::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        $pendingSchool = User::factory()->create([
            'name' => 'SMK Pending',
            'role' => User::ROLE_SEKOLAH,
            'account_status' => User::STATUS_PENDING,
            'is_active' => false,
        ]);

        SchoolProfile::query()->create([
            'user_id' => $approvedSchool->id,
            'school_name' => 'SMK Approved',
            'npsn' => '10101010',
            'accreditation' => 'A',
            'address' => 'Jl. Approved School',
            'expertise_fields' => ['RPL'],
            'logo_path' => 'profiles/schools/logos/approved.jpg',
            'short_description' => 'Sekolah approved.',
            'operational_license_path' => 'profiles/schools/legalities/approved.pdf',
        ]);

        SchoolProfile::query()->create([
            'user_id' => $pendingSchool->id,
            'school_name' => 'SMK Pending',
            'npsn' => '20202020',
            'accreditation' => 'B',
            'address' => 'Jl. Pending School',
            'expertise_fields' => ['TKJ'],
            'logo_path' => 'profiles/schools/logos/pending.jpg',
            'short_description' => 'Sekolah pending.',
            'operational_license_path' => 'profiles/schools/legalities/pending.pdf',
        ]);

        $approvedMitra = User::factory()->create([
            'name' => 'PT Approved Mitra',
            'role' => User::ROLE_MITRA,
            'mitra_type' => User::MITRA_PERUSAHAAN,
            'account_status' => User::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        $pendingMitra = User::factory()->create([
            'name' => 'PT Pending Mitra',
            'role' => User::ROLE_MITRA,
            'mitra_type' => User::MITRA_PERUSAHAAN,
            'account_status' => User::STATUS_PENDING,
            'is_active' => false,
        ]);

        CompanyProfile::query()->create([
            'user_id' => $approvedMitra->id,
            'company_name' => 'PT Approved Mitra',
            'nib' => '3333333333333',
            'industry_sector' => 'Teknologi',
            'employee_total_range' => '51-200',
            'office_address' => 'Bandung',
            'website_or_social_url' => null,
            'short_description' => 'Mitra approved.',
            'company_logo_path' => 'profiles/companies/logos/approved.jpg',
            'kemenkumham_decree_path' => 'profiles/companies/legalities/approved.pdf',
        ]);

        CompanyProfile::query()->create([
            'user_id' => $pendingMitra->id,
            'company_name' => 'PT Pending Mitra',
            'nib' => '4444444444444',
            'industry_sector' => 'Teknologi',
            'employee_total_range' => '51-200',
            'office_address' => 'Jakarta',
            'website_or_social_url' => null,
            'short_description' => 'Mitra pending.',
            'company_logo_path' => 'profiles/companies/logos/pending.jpg',
            'kemenkumham_decree_path' => 'profiles/companies/legalities/pending.pdf',
        ]);

        $schoolResponse = $this->getJson('/api/public/schools');
        $mitraResponse = $this->getJson('/api/public/mitra');

        $schoolResponse->assertStatus(200)
            ->assertJsonPath('meta.pagination.total', 1)
            ->assertJsonPath('data.0.id', $approvedSchool->id)
            ->assertJsonMissing([
                'id' => $pendingSchool->id,
            ]);

        $mitraResponse->assertStatus(200)
            ->assertJsonPath('meta.pagination.total', 1)
            ->assertJsonPath('data.0.id', $approvedMitra->id)
            ->assertJsonMissing([
                'id' => $pendingMitra->id,
            ]);
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
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['token', 'token_type', 'user', 'role_profile', 'requires_skill_setup', 'next_step'],
                'errors',
                'meta',
            ])
            ->assertJsonPath('data.requires_skill_setup', true)
            ->assertJsonPath('data.next_step', 'add_skill')
            ->assertJsonPath('data.user.role', User::ROLE_SISWA)
            ->assertJsonPath('data.role_profile.nisn', '1234567890');

        $response->assertJsonMissingPath('data.user.student_profile');

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
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.requires_skill_setup', false)
            ->assertJsonPath('data.next_step', null)
            ->assertJsonPath('data.role_profile.nisn', '5556667778');
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
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['token', 'token_type', 'user', 'role_profile', 'requires_skill_setup', 'next_step'],
                'errors',
                'meta',
            ])
            ->assertJsonPath('data.role_profile', null);

        $this->assertNotNull($school->fresh()->last_login_at);
    }

    public function test_school_profile_endpoint_returns_role_specific_profile_data(): void
    {
        $school = User::factory()->create([
            'role' => User::ROLE_SEKOLAH,
            'account_status' => 'active',
            'is_active' => true,
        ]);

        SchoolProfile::query()->create([
            'user_id' => $school->id,
            'school_name' => 'SMK Profil Universal',
            'npsn' => '87654321',
            'accreditation' => 'A',
            'address' => 'Jl. Universal No. 1',
            'expertise_fields' => ['RPL', 'TKJ'],
            'logo_path' => 'schools/logo.png',
            'short_description' => 'Sekolah untuk pengujian endpoint profile universal.',
            'operational_license_path' => 'schools/license.pdf',
        ]);

        Sanctum::actingAs($school);

        $response = $this->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.role', User::ROLE_SEKOLAH)
            ->assertJsonPath('data.role_profile.school_name', 'SMK Profil Universal')
            ->assertJsonPath('data.role_profile.npsn', '87654321');

        $response->assertJsonMissingPath('data.user.school_profile');
    }

    public function test_admin_profile_endpoint_can_create_and_update_admin_profile(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'account_status' => 'active',
            'is_active' => true,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->putJson('/api/profile', [
            'profile' => [
                'full_name' => 'Admin Utama',
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.role', User::ROLE_ADMIN)
            ->assertJsonPath('data.role_profile.full_name', 'Admin Utama');

        $this->assertDatabaseHas('admin_profiles', [
            'user_id' => $admin->id,
            'full_name' => 'Admin Utama',
        ]);

        $this->assertInstanceOf(AdminProfile::class, $admin->fresh()->adminProfile);
    }

    public function test_admin_profile_update_rejects_unsupported_fields(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'account_status' => 'active',
            'is_active' => true,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->putJson('/api/profile', [
            'profile' => [
                'phone_number' => '081234567890',
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_student_profile_update_via_profile_endpoint_returns_standardized_response(): void
    {
        $student = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'account_status' => 'active',
            'is_active' => true,
        ]);

        StudentProfile::query()->create([
            'user_id' => $student->id,
            'full_name' => 'Siswa Uji',
            'nisn' => '1112223334',
            'major' => 'RPL',
            'school_origin' => 'SMK Negeri 1',
            'graduation_status' => 'Belum Lulus',
            'unique_code' => 'ABCD1234',
        ]);

        Sanctum::actingAs($student);

        $response = $this->putJson('/api/profile', [
            'description' => 'Siswa fokus backend Laravel.',
            'phone_number' => '081234567890',
            'address' => 'Jakarta Selatan',
            'class_year' => '2026',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Profil berhasil diperbarui.')
            ->assertJsonPath('data.user.role', User::ROLE_SISWA)
            ->assertJsonPath('data.role_profile.description', 'Siswa fokus backend Laravel.')
            ->assertJsonPath('data.role_profile.phone_number', '081234567890')
            ->assertJsonPath('data.role_profile.address', 'Jakarta Selatan')
            ->assertJsonPath('data.role_profile.class_year', '2026');

        $this->assertDatabaseHas('student_profiles', [
            'user_id' => $student->id,
            'description' => 'Siswa fokus backend Laravel.',
            'phone_number' => '081234567890',
            'address' => 'Jakarta Selatan',
            'class_year' => '2026',
        ]);
    }
}
