<?php

namespace Tests\Feature;

use App\Models\CompanyProfile;
use App\Models\PartnershipProposal;
use App\Models\SchoolProfile;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfilePartnershipApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_profile_includes_mitra_partnership_proposals(): void
    {
        $school = User::factory()->create([
            'role' => User::ROLE_SEKOLAH,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        SchoolProfile::query()->create([
            'user_id' => $school->id,
            'school_name' => 'SMK Test',
            'npsn' => '11111111',
            'accreditation' => 'A',
            'address' => 'Jakarta',
            'expertise_fields' => ['RPL'],
            'logo_path' => 'test.jpg',
            'short_description' => 'Test.',
            'operational_license_path' => 'test.pdf',
        ]);

        $mitra = User::factory()->create([
            'role' => User::ROLE_MITRA,
            'mitra_type' => User::MITRA_PERUSAHAAN,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        CompanyProfile::query()->create([
            'user_id' => $mitra->id,
            'company_name' => 'PT Partner',
            'nib' => '1234567890123',
            'industry_sector' => 'IT',
            'employee_total_range' => '1-10',
            'office_address' => 'Jakarta',
            'short_description' => 'Test.',
            'company_logo_path' => 'test.jpg',
            'kemenkumham_decree_path' => 'test.pdf',
        ]);

        PartnershipProposal::query()->create([
            'proposer_user_id' => $mitra->id,
            'target_user_id' => $school->id,
            'school_user_id' => $school->id,
            'mitra_user_id' => $mitra->id,
            'proposal_pdf_path' => 'test.pdf',
            'notes' => 'Test proposal.',
            'status' => PartnershipProposal::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($school);

        $response = $this->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'role_profile' => [
                        'pengajuan_mitra' => [
                            '*' => [
                                'mitra_user_id',
                                'nama_mitra',
                                'sektor_atau_tipe',
                                'mitra_tipe',
                                'tanggal_submit',
                                'status_submit',
                            ],
                        ],
                    ],
                ],
            ]);

        $roleProfile = $response->json('data.role_profile');
        $this->assertCount(1, $roleProfile['pengajuan_mitra']);
        $this->assertEquals('PT Partner', $roleProfile['pengajuan_mitra'][0]['nama_mitra']);
        $this->assertEquals('submitted', $roleProfile['pengajuan_mitra'][0]['status_submit']);
    }

    public function test_mitra_perusahaan_profile_includes_school_partnership_proposals(): void
    {
        $mitra = User::factory()->create([
            'role' => User::ROLE_MITRA,
            'mitra_type' => User::MITRA_PERUSAHAAN,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        CompanyProfile::query()->create([
            'user_id' => $mitra->id,
            'company_name' => 'PT Mitra Test',
            'nib' => '9876543210123',
            'industry_sector' => 'Teknologi',
            'employee_total_range' => '51-200',
            'office_address' => 'Jakarta',
            'short_description' => 'Test.',
            'company_logo_path' => 'test.jpg',
            'kemenkumham_decree_path' => 'test.pdf',
        ]);

        $school = User::factory()->create([
            'role' => User::ROLE_SEKOLAH,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        SchoolProfile::query()->create([
            'user_id' => $school->id,
            'school_name' => 'SMK Maju',
            'npsn' => '22222222',
            'accreditation' => 'B',
            'address' => 'Bogor',
            'expertise_fields' => ['TKJ'],
            'logo_path' => 'test.jpg',
            'short_description' => 'Test.',
            'operational_license_path' => 'test.pdf',
        ]);

        PartnershipProposal::query()->create([
            'proposer_user_id' => $school->id,
            'target_user_id' => $mitra->id,
            'school_user_id' => $school->id,
            'mitra_user_id' => $mitra->id,
            'proposal_pdf_path' => 'test.pdf',
            'notes' => 'Test proposal.',
            'status' => PartnershipProposal::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($mitra);

        $response = $this->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'role_profile' => [
                        'pengajuan_sekolah' => [
                            '*' => [
                                'school_user_id',
                                'nama_sekolah',
                                'akreditasi',
                                'tanggal_submit',
                                'status_submit',
                            ],
                        ],
                    ],
                ],
            ]);

        $roleProfile = $response->json('data.role_profile');
        $this->assertCount(1, $roleProfile['pengajuan_sekolah']);
        $this->assertEquals('SMK Maju', $roleProfile['pengajuan_sekolah'][0]['nama_sekolah']);
        $this->assertEquals('B', $roleProfile['pengajuan_sekolah'][0]['akreditasi']);
    }

    public function test_mitra_umkm_profile_includes_school_partnership_proposals(): void
    {
        $mitra = User::factory()->create([
            'role' => User::ROLE_MITRA,
            'mitra_type' => User::MITRA_UMKM,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        UmkmProfile::query()->create([
            'user_id' => $mitra->id,
            'business_name' => 'UMKM Test',
            'owner_nik' => '3201234567890001',
            'business_type' => 'Kuliner',
            'business_address' => 'Depok',
            'short_description' => 'Test.',
            'umkm_logo_path' => 'test.jpg',
            'owner_ktp_photo_path' => 'test.jpg',
            'image_1_path' => 'test1.jpg',
            'image_2_path' => 'test2.jpg',
            'image_3_path' => 'test3.jpg',
            'image_4_path' => 'test4.jpg',
            'image_5_path' => 'test5.jpg',
        ]);

        $school = User::factory()->create([
            'role' => User::ROLE_SEKOLAH,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        SchoolProfile::query()->create([
            'user_id' => $school->id,
            'school_name' => 'SMK Hebat',
            'npsn' => '33333333',
            'accreditation' => 'A',
            'address' => 'Tangerang',
            'expertise_fields' => ['AKL'],
            'logo_path' => 'test.jpg',
            'short_description' => 'Test.',
            'operational_license_path' => 'test.pdf',
        ]);

        PartnershipProposal::query()->create([
            'proposer_user_id' => $school->id,
            'target_user_id' => $mitra->id,
            'school_user_id' => $school->id,
            'mitra_user_id' => $mitra->id,
            'proposal_pdf_path' => 'test.pdf',
            'notes' => 'Test proposal.',
            'status' => PartnershipProposal::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($mitra);

        $response = $this->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'role_profile' => [
                        'pengajuan_sekolah' => [
                            '*' => [
                                'school_user_id',
                                'nama_sekolah',
                                'akreditasi',
                                'tanggal_submit',
                                'status_submit',
                            ],
                        ],
                    ],
                ],
            ]);

        $roleProfile = $response->json('data.role_profile');
        $this->assertCount(1, $roleProfile['pengajuan_sekolah']);
        $this->assertEquals('SMK Hebat', $roleProfile['pengajuan_sekolah'][0]['nama_sekolah']);
    }

    public function test_school_profile_mitra_partnership_proposals_empty_when_no_proposals(): void
    {
        $school = User::factory()->create([
            'role' => User::ROLE_SEKOLAH,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        SchoolProfile::query()->create([
            'user_id' => $school->id,
            'school_name' => 'SMK Kosong',
            'npsn' => '44444444',
            'accreditation' => 'C',
            'address' => 'Bekasi',
            'expertise_fields' => ['MM'],
            'logo_path' => 'test.jpg',
            'short_description' => 'Test.',
            'operational_license_path' => 'test.pdf',
        ]);

        Sanctum::actingAs($school);

        $response = $this->getJson('/api/profile');

        $response->assertStatus(200);

        $roleProfile = $response->json('data.role_profile');
        $this->assertArrayHasKey('pengajuan_mitra', $roleProfile);
        $this->assertCount(0, $roleProfile['pengajuan_mitra']);
    }

    public function test_mitra_profile_excludes_non_submitted_proposals(): void
    {
        $mitra = User::factory()->create([
            'role' => User::ROLE_MITRA,
            'mitra_type' => User::MITRA_PERUSAHAAN,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        CompanyProfile::query()->create([
            'user_id' => $mitra->id,
            'company_name' => 'PT Filter Test',
            'nib' => '5555555555555',
            'industry_sector' => 'IT',
            'employee_total_range' => '1-10',
            'office_address' => 'Jakarta',
            'short_description' => 'Test.',
            'company_logo_path' => 'test.jpg',
            'kemenkumham_decree_path' => 'test.pdf',
        ]);

        $school = User::factory()->create([
            'role' => User::ROLE_SEKOLAH,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        SchoolProfile::query()->create([
            'user_id' => $school->id,
            'school_name' => 'SMK Approved',
            'npsn' => '55555555',
            'accreditation' => 'A',
            'address' => 'Surabaya',
            'expertise_fields' => ['RPL'],
            'logo_path' => 'test.jpg',
            'short_description' => 'Test.',
            'operational_license_path' => 'test.pdf',
        ]);

        PartnershipProposal::query()->create([
            'proposer_user_id' => $school->id,
            'target_user_id' => $mitra->id,
            'school_user_id' => $school->id,
            'mitra_user_id' => $mitra->id,
            'proposal_pdf_path' => 'test.pdf',
            'notes' => 'Approved proposal.',
            'status' => 'approved',
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($mitra);

        $response = $this->getJson('/api/profile');

        $response->assertStatus(200);

        $roleProfile = $response->json('data.role_profile');
        $this->assertArrayHasKey('pengajuan_sekolah', $roleProfile);
        $this->assertCount(0, $roleProfile['pengajuan_sekolah']);
    }
}
