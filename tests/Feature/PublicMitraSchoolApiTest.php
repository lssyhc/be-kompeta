<?php

namespace Tests\Feature;

use App\Models\CompanyProfile;
use App\Models\PartnershipProposal;
use App\Models\SchoolProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicMitraSchoolApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mitra_detail_includes_school_partnership_proposals(): void
    {
        Storage::fake('local');

        $mitra = User::factory()->create([
            'role' => User::ROLE_MITRA,
            'mitra_type' => User::MITRA_PERUSAHAAN,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        CompanyProfile::query()->create([
            'user_id' => $mitra->id,
            'company_name' => 'PT Test Mitra',
            'nib' => '9999999999999',
            'industry_sector' => 'Teknologi',
            'employee_total_range' => '51-200',
            'office_address' => 'Jakarta',
            'short_description' => 'Mitra test.',
            'company_logo_path' => 'profiles/companies/logos/test.jpg',
            'kemenkumham_decree_path' => 'profiles/companies/legalities/test.pdf',
        ]);

        $school = User::factory()->create([
            'role' => User::ROLE_SEKOLAH,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        SchoolProfile::query()->create([
            'user_id' => $school->id,
            'school_name' => 'SMK Uji Coba',
            'npsn' => '88888888',
            'accreditation' => 'A',
            'address' => 'Bandung',
            'expertise_fields' => ['RPL'],
            'logo_path' => 'profiles/schools/logos/test.jpg',
            'short_description' => 'Sekolah test.',
            'operational_license_path' => 'profiles/schools/legalities/test.pdf',
        ]);

        PartnershipProposal::query()->create([
            'proposer_user_id' => $school->id,
            'target_user_id' => $mitra->id,
            'school_user_id' => $school->id,
            'mitra_user_id' => $mitra->id,
            'proposal_pdf_path' => 'partnership/proposals/test.pdf',
            'notes' => 'Proposal kemitraan test.',
            'status' => PartnershipProposal::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $response = $this->getJson("/api/public/mitra/{$mitra->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'mitra_type',
                    'name',
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
            ])
            ->assertJsonPath('data.pengajuan_sekolah.0.school_user_id', $school->id)
            ->assertJsonPath('data.pengajuan_sekolah.0.nama_sekolah', 'SMK Uji Coba')
            ->assertJsonPath('data.pengajuan_sekolah.0.akreditasi', 'A')
            ->assertJsonPath('data.pengajuan_sekolah.0.status_submit', 'submitted');
    }

    public function test_mitra_detail_school_partnership_proposals_empty_when_no_proposals(): void
    {
        $mitra = User::factory()->create([
            'role' => User::ROLE_MITRA,
            'mitra_type' => User::MITRA_PERUSAHAAN,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        CompanyProfile::query()->create([
            'user_id' => $mitra->id,
            'company_name' => 'PT No Proposals',
            'nib' => '8888888888888',
            'industry_sector' => 'Distribusi',
            'employee_total_range' => '11-50',
            'office_address' => 'Surabaya',
            'short_description' => 'Mitra tanpa pengajuan.',
            'company_logo_path' => 'profiles/companies/logos/no-proposals.jpg',
            'kemenkumham_decree_path' => 'profiles/companies/legalities/no-proposals.pdf',
        ]);

        $response = $this->getJson("/api/public/mitra/{$mitra->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.pengajuan_sekolah', []);
    }

    public function test_school_detail_includes_mitra_partnership_proposals(): void
    {
        Storage::fake('local');

        $school = User::factory()->create([
            'role' => User::ROLE_SEKOLAH,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        SchoolProfile::query()->create([
            'user_id' => $school->id,
            'school_name' => 'SMK Partnership Test',
            'npsn' => '77777777',
            'accreditation' => 'A',
            'address' => 'Jakarta',
            'expertise_fields' => ['TKJ'],
            'logo_path' => 'profiles/schools/logos/partnership-test.jpg',
            'short_description' => 'Sekolah untuk test partnership.',
            'operational_license_path' => 'profiles/schools/legalities/partnership-test.pdf',
        ]);

        $mitra = User::factory()->create([
            'role' => User::ROLE_MITRA,
            'mitra_type' => User::MITRA_PERUSAHAAN,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        CompanyProfile::query()->create([
            'user_id' => $mitra->id,
            'company_name' => 'PT Mitra Partner',
            'nib' => '6666666666666',
            'industry_sector' => 'Manufaktur',
            'employee_total_range' => '201-500',
            'office_address' => 'Bekasi',
            'short_description' => 'Mitra untuk test.',
            'company_logo_path' => 'profiles/companies/logos/partner.jpg',
            'kemenkumham_decree_path' => 'profiles/companies/legalities/partner.pdf',
        ]);

        PartnershipProposal::query()->create([
            'proposer_user_id' => $mitra->id,
            'target_user_id' => $school->id,
            'school_user_id' => $school->id,
            'mitra_user_id' => $mitra->id,
            'proposal_pdf_path' => 'partnership/proposals/partner-test.pdf',
            'notes' => 'Proposal dari mitra.',
            'status' => PartnershipProposal::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $response = $this->getJson("/api/public/schools/{$school->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'school_name',
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
            ])
            ->assertJsonPath('data.pengajuan_mitra.0.mitra_user_id', $mitra->id)
            ->assertJsonPath('data.pengajuan_mitra.0.nama_mitra', 'PT Mitra Partner')
            ->assertJsonPath('data.pengajuan_mitra.0.sektor_atau_tipe', 'Manufaktur')
            ->assertJsonPath('data.pengajuan_mitra.0.status_submit', 'submitted');
    }
}
