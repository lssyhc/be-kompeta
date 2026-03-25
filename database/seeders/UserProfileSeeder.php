<?php

namespace Database\Seeders;

use App\Models\AdminProfile;
use App\Models\CompanyProfile;
use App\Models\SchoolProfile;
use App\Models\StudentProfile;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class UserProfileSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAdmin();
        $this->seedMitra();
        $this->seedSchools();
        $this->seedStudents();
        $this->seedBulkSchools();
        $this->seedBulkMitra();
        $this->seedLargeSchoolWithStudents();
    }

    private function seedAdmin(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@kompeta.test'],
            [
                'name' => 'System Admin',
                'password' => 'admin12345',
                'role' => User::ROLE_ADMIN,
                'account_status' => User::STATUS_ACTIVE,
            ]
        );

        AdminProfile::query()->updateOrCreate(
            ['user_id' => $admin->id],
            [
                'full_name' => $admin->name,
                'avatar_path' => User::DEFAULT_PROFILE_PHOTO_PATH,
            ]
        );
    }

    private function seedMitra(): void
    {
        $companyA = User::query()->updateOrCreate(
            ['email' => 'mitra.company.a@kompeta.test'],
            [
                'name' => 'PT Binajasa Sumber Sarana',
                'password' => 'password123',
                'role' => User::ROLE_MITRA,
                'mitra_type' => User::MITRA_PERUSAHAAN,
                'account_status' => User::STATUS_ACTIVE,
            ]
        );

        CompanyProfile::query()->updateOrCreate(
            ['user_id' => $companyA->id],
            [
                'company_name' => 'PT Binajasa Sumber Sarana',
                'nib' => '1234567890123',
                'industry_sector' => 'Outsourcing dan layanan tenaga kerja',
                'employee_total_range' => '501-1000',
                'office_address' => 'Jakarta Selatan, DKI Jakarta',
                'socials' => [
                    'website' => 'https://example.com/binajasa',
                    'instagram' => 'https://instagram.com/binajasa',
                    'linkedin' => 'https://linkedin.com/company/binajasa',
                    'whatsapp' => '6281234567890',
                ],
                'short_description' => 'Perusahaan jasa tenaga kerja untuk operasional bisnis dan layanan office support.',
                'company_logo_path' => 'https://via.placeholder.com/200x200?text=Binajasa',
                'image_1_path' => 'https://via.placeholder.com/400x300?text=Binajasa+1',
                'image_2_path' => 'https://via.placeholder.com/400x300?text=Binajasa+2',
                'image_3_path' => 'https://via.placeholder.com/400x300?text=Binajasa+3',
                'image_4_path' => 'https://via.placeholder.com/400x300?text=Binajasa+4',
                'image_5_path' => 'https://via.placeholder.com/400x300?text=Binajasa+5',
                'kemenkumham_decree_path' => $this->storeLocalFile('profiles/companies/legalities/binajasa-sk.pdf', 'SK Kemenkumham Binajasa'),
            ]
        );

        $companyB = User::query()->updateOrCreate(
            ['email' => 'mitra.company.b@kompeta.test'],
            [
                'name' => 'PT Surya Data Nusantara',
                'password' => 'password123',
                'role' => User::ROLE_MITRA,
                'mitra_type' => User::MITRA_PERUSAHAAN,
                'account_status' => User::STATUS_ACTIVE,
            ]
        );

        CompanyProfile::query()->updateOrCreate(
            ['user_id' => $companyB->id],
            [
                'company_name' => 'PT Surya Data Nusantara',
                'nib' => '1234567890124',
                'industry_sector' => 'Teknologi informasi',
                'employee_total_range' => '201-500',
                'office_address' => 'Bandung, Jawa Barat',
                'socials' => [
                    'website' => 'https://example.com/surya-data',
                    'instagram' => 'https://instagram.com/suryadata',
                    'linkedin' => null,
                    'whatsapp' => '6289876543210',
                ],
                'short_description' => 'Mitra digitalisasi operasional dan pengolahan data untuk bisnis menengah.',
                'company_logo_path' => 'https://via.placeholder.com/200x200?text=Surya',
                'image_1_path' => 'https://via.placeholder.com/400x300?text=Surya+Data+1',
                'image_2_path' => 'https://via.placeholder.com/400x300?text=Surya+Data+2',
                'image_3_path' => 'https://via.placeholder.com/400x300?text=Surya+Data+3',
                'image_4_path' => 'https://via.placeholder.com/400x300?text=Surya+Data+4',
                'image_5_path' => 'https://via.placeholder.com/400x300?text=Surya+Data+5',
                'kemenkumham_decree_path' => $this->storeLocalFile('profiles/companies/legalities/surya-data-sk.pdf', 'SK Kemenkumham Surya Data'),
            ]
        );

        $umkmA = User::query()->updateOrCreate(
            ['email' => 'mitra.umkm.a@kompeta.test'],
            [
                'name' => 'Roti Bunda Rasa',
                'password' => 'password123',
                'role' => User::ROLE_MITRA,
                'mitra_type' => User::MITRA_UMKM,
                'account_status' => User::STATUS_ACTIVE,
            ]
        );

        UmkmProfile::query()->updateOrCreate(
            ['user_id' => $umkmA->id],
            [
                'business_name' => 'Roti Bunda Rasa',
                'owner_nik' => '3201234567891111',
                'owner_personal_nib' => 'NIB-UMKM-001',
                'business_type' => 'Kuliner',
                'business_address' => 'Semarang, Jawa Tengah',
                'socials' => [
                    'website' => null,
                    'instagram' => 'https://instagram.com/rotibunda',
                    'linkedin' => null,
                    'whatsapp' => '6281111222333',
                ],
                'umkm_logo_path' => 'https://via.placeholder.com/200x200?text=Roti+Bunda',
                'owner_ktp_photo_path' => $this->storeLocalFile('profiles/umkm/ktp/roti-bunda-ktp.jpg', 'KTP Owner Roti Bunda Rasa'),
                'short_description' => 'UMKM kuliner rumahan yang fokus pada bakery harian dan pemesanan acara.',
                'image_1_path' => 'https://via.placeholder.com/400x300?text=Roti+Bunda+1',
                'image_2_path' => 'https://via.placeholder.com/400x300?text=Roti+Bunda+2',
                'image_3_path' => 'https://via.placeholder.com/400x300?text=Roti+Bunda+3',
                'image_4_path' => 'https://via.placeholder.com/400x300?text=Roti+Bunda+4',
                'image_5_path' => 'https://via.placeholder.com/400x300?text=Roti+Bunda+5',
            ]
        );

        $umkmB = User::query()->updateOrCreate(
            ['email' => 'mitra.umkm.b@kompeta.test'],
            [
                'name' => 'Craft Kayu Jogja',
                'password' => 'password123',
                'role' => User::ROLE_MITRA,
                'mitra_type' => User::MITRA_UMKM,
                'account_status' => User::STATUS_ACTIVE,
            ]
        );

        UmkmProfile::query()->updateOrCreate(
            ['user_id' => $umkmB->id],
            [
                'business_name' => 'Craft Kayu Jogja',
                'owner_nik' => '3201234567892222',
                'owner_personal_nib' => 'NIB-UMKM-002',
                'business_type' => 'Kerajinan',
                'business_address' => 'Sleman, DI Yogyakarta',
                'socials' => [
                    'website' => 'https://example.com/craft-kayu',
                    'instagram' => 'https://instagram.com/craftkayujogja',
                    'linkedin' => null,
                    'whatsapp' => '6282222333444',
                ],
                'umkm_logo_path' => 'https://via.placeholder.com/200x200?text=Craft+Kayu',
                'owner_ktp_photo_path' => $this->storeLocalFile('profiles/umkm/ktp/craft-kayu-ktp.jpg', 'KTP Owner Craft Kayu Jogja'),
                'short_description' => 'UMKM kerajinan kayu untuk kebutuhan dekorasi rumah, hotel, dan kantor.',
                'image_1_path' => 'https://via.placeholder.com/400x300?text=Craft+Kayu+Jogja+1',
                'image_2_path' => 'https://via.placeholder.com/400x300?text=Craft+Kayu+Jogja+2',
                'image_3_path' => 'https://via.placeholder.com/400x300?text=Craft+Kayu+Jogja+3',
                'image_4_path' => 'https://via.placeholder.com/400x300?text=Craft+Kayu+Jogja+4',
                'image_5_path' => 'https://via.placeholder.com/400x300?text=Craft+Kayu+Jogja+5',
            ]
        );

        $pendingMitra = User::query()->updateOrCreate(
            ['email' => 'mitra.pending@kompeta.test'],
            [
                'name' => 'PT Pending Nusantara',
                'password' => 'password123',
                'role' => User::ROLE_MITRA,
                'mitra_type' => User::MITRA_PERUSAHAAN,
                'account_status' => User::STATUS_PENDING,
            ]
        );

        CompanyProfile::query()->updateOrCreate(
            ['user_id' => $pendingMitra->id],
            [
                'company_name' => 'PT Pending Nusantara',
                'nib' => '1234567890199',
                'industry_sector' => 'Distribusi',
                'employee_total_range' => '11-50',
                'office_address' => 'Surabaya, Jawa Timur',
                'socials' => [
                    'website' => 'https://example.com/pending-nusantara',
                    'instagram' => null,
                    'linkedin' => null,
                    'whatsapp' => null,
                ],
                'short_description' => 'Perusahaan distribusi yang menunggu persetujuan verifikasi.',
                'company_logo_path' => 'https://via.placeholder.com/200x200?text=Pending',
                'image_1_path' => null,
                'image_2_path' => null,
                'image_3_path' => null,
                'image_4_path' => null,
                'image_5_path' => null,
                'kemenkumham_decree_path' => $this->storeLocalFile('profiles/companies/legalities/pending-nusantara-sk.pdf', 'SK Kemenkumham Pending Nusantara'),
            ]
        );

        $rejectedMitra = User::query()->updateOrCreate(
            ['email' => 'mitra.rejected@kompeta.test'],
            [
                'name' => 'UMKM Ditolak Jaya',
                'password' => 'password123',
                'role' => User::ROLE_MITRA,
                'mitra_type' => User::MITRA_UMKM,
                'account_status' => User::STATUS_REJECTED,
            ]
        );

        UmkmProfile::query()->updateOrCreate(
            ['user_id' => $rejectedMitra->id],
            [
                'business_name' => 'UMKM Ditolak Jaya',
                'owner_nik' => '3201234567893333',
                'owner_personal_nib' => null,
                'business_type' => 'Fashion',
                'business_address' => 'Depok, Jawa Barat',
                'socials' => UmkmProfile::DEFAULT_SOCIALS,
                'umkm_logo_path' => 'https://via.placeholder.com/200x200?text=Rejected',
                'owner_ktp_photo_path' => $this->storeLocalFile('profiles/umkm/ktp/rejected-jaya-ktp.jpg', 'KTP Owner UMKM Ditolak Jaya'),
                'short_description' => 'Data UMKM contoh untuk status registrasi rejected.',
                'image_1_path' => 'https://via.placeholder.com/400x300?text=Rejected+1',
                'image_2_path' => 'https://via.placeholder.com/400x300?text=Rejected+2',
                'image_3_path' => 'https://via.placeholder.com/400x300?text=Rejected+3',
                'image_4_path' => 'https://via.placeholder.com/400x300?text=Rejected+4',
                'image_5_path' => 'https://via.placeholder.com/400x300?text=Rejected+5',
            ]
        );
    }

    private function seedSchools(): void
    {
        $schoolA = User::query()->updateOrCreate(
            ['email' => 'school.a@kompeta.test'],
            [
                'name' => 'SMA Negeri 1 Bogor',
                'password' => 'password123',
                'role' => User::ROLE_SEKOLAH,
                'account_status' => User::STATUS_ACTIVE,
            ]
        );

        SchoolProfile::query()->updateOrCreate(
            ['user_id' => $schoolA->id],
            [
                'school_name' => 'SMA Negeri 1 Bogor',
                'npsn' => '20201234',
                'accreditation' => 'A',
                'address' => 'Jl. Ahmad Yani No. 1, Bogor, Jawa Barat',
                'socials' => [
                    'website' => 'https://sman1bogor.sch.id',
                    'instagram' => 'https://instagram.com/sman1bogor',
                    'linkedin' => null,
                    'whatsapp' => '6281234000001',
                ],
                'expertise_fields' => ['IPA', 'IPS', 'Bahasa'],
                'logo_path' => 'https://via.placeholder.com/200x200?text=SMA+Negeri+1',
                'image_1_path' => 'https://via.placeholder.com/400x300?text=SMA+Bogor+1',
                'image_2_path' => 'https://via.placeholder.com/400x300?text=SMA+Bogor+2',
                'image_3_path' => 'https://via.placeholder.com/400x300?text=SMA+Bogor+3',
                'image_4_path' => 'https://via.placeholder.com/400x300?text=SMA+Bogor+4',
                'image_5_path' => 'https://via.placeholder.com/400x300?text=SMA+Bogor+5',
                'short_description' => 'SMA Negeri 1 Bogor berfokus pada pengembangan akademik, karakter, dan kesiapan karier siswa.',
                'operational_license_path' => $this->storeLocalFile('profiles/schools/legalities/sman1-bogor-license.pdf', 'Izin Operasional SMA Negeri 1 Bogor'),
            ]
        );

        $schoolB = User::query()->updateOrCreate(
            ['email' => 'school.b@kompeta.test'],
            [
                'name' => 'SMA Negeri 2 Jakarta',
                'password' => 'password123',
                'role' => User::ROLE_SEKOLAH,
                'account_status' => User::STATUS_ACTIVE,
            ]
        );

        SchoolProfile::query()->updateOrCreate(
            ['user_id' => $schoolB->id],
            [
                'school_name' => 'SMA Negeri 2 Jakarta',
                'npsn' => '20205678',
                'accreditation' => 'A',
                'address' => 'Jl. Sudirman No. 100, Jakarta Pusat, DKI Jakarta',
                'socials' => [
                    'website' => 'https://sman2jakarta.sch.id',
                    'instagram' => null,
                    'linkedin' => null,
                    'whatsapp' => null,
                ],
                'expertise_fields' => ['IPA', 'IPS'],
                'logo_path' => 'https://via.placeholder.com/200x200?text=SMA+Negeri+2',
                'image_1_path' => 'https://via.placeholder.com/400x300?text=SMA+Jakarta+1',
                'image_2_path' => 'https://via.placeholder.com/400x300?text=SMA+Jakarta+2',
                'image_3_path' => null,
                'image_4_path' => null,
                'image_5_path' => null,
                'short_description' => 'SMA Negeri 2 Jakarta merupakan institusi pendidikan unggulan di Jakarta Pusat.',
                'operational_license_path' => $this->storeLocalFile('profiles/schools/legalities/sman2-jakarta-license.pdf', 'Izin Operasional SMA Negeri 2 Jakarta'),
            ]
        );

        $pendingSchool = User::query()->updateOrCreate(
            ['email' => 'school.pending@kompeta.test'],
            [
                'name' => 'SMK Pending Sejahtera',
                'password' => 'password123',
                'role' => User::ROLE_SEKOLAH,
                'account_status' => User::STATUS_PENDING,
            ]
        );

        SchoolProfile::query()->updateOrCreate(
            ['user_id' => $pendingSchool->id],
            [
                'school_name' => 'SMK Pending Sejahtera',
                'npsn' => '20209999',
                'accreditation' => 'B',
                'address' => 'Malang, Jawa Timur',
                'socials' => SchoolProfile::DEFAULT_SOCIALS,
                'expertise_fields' => ['DKV', 'MM'],
                'logo_path' => 'https://via.placeholder.com/200x200?text=SMK+Pending',
                'image_1_path' => null,
                'image_2_path' => null,
                'image_3_path' => null,
                'image_4_path' => null,
                'image_5_path' => null,
                'short_description' => 'Data sekolah contoh untuk status pending approval.',
                'operational_license_path' => $this->storeLocalFile('profiles/schools/legalities/smk-pending-license.pdf', 'Izin Operasional SMK Pending Sejahtera'),
            ]
        );
    }

    private function seedStudents(): void
    {
        $schoolA = User::query()->where('email', 'school.a@kompeta.test')->firstOrFail();
        $schoolB = User::query()->where('email', 'school.b@kompeta.test')->firstOrFail();

        $studentA1 = User::query()->updateOrCreate(
            ['email' => 'student.a1@kompeta.test'],
            [
                'name' => 'Budi Santoso',
                'password' => 'password123',
                'role' => User::ROLE_SISWA,
                'account_status' => User::STATUS_ACTIVE,
            ]
        );

        StudentProfile::query()->updateOrCreate(
            ['nisn' => '0001234567'],
            [
                'user_id' => $studentA1->id,
                'school_user_id' => $schoolA->id,
                'full_name' => 'Budi Santoso',
                'photo_profile_path' => User::DEFAULT_PROFILE_PHOTO_PATH,
                'major' => 'IPA',
                'school_origin' => 'SMA Negeri 1 Bogor',
                'graduation_status' => 'graduated',
                'class_year' => '2024',
                'unique_code' => 'BUDI0001234X9YZ',
                'description' => 'Siswa berprestasi dengan fokus bidang sains dan data.',
                'socials' => [
                    'website' => null,
                    'instagram' => 'https://instagram.com/budisantoso',
                    'linkedin' => null,
                    'whatsapp' => '082123456789',
                ],
                'address' => 'Bogor, Jawa Barat',
            ]
        );

        $studentA2 = User::query()->updateOrCreate(
            ['email' => 'student.a2@kompeta.test'],
            [
                'name' => 'Siti Rahayu',
                'password' => 'password123',
                'role' => User::ROLE_SISWA,
                'account_status' => User::STATUS_ACTIVE,
            ]
        );

        StudentProfile::query()->updateOrCreate(
            ['nisn' => '0001234568'],
            [
                'user_id' => $studentA2->id,
                'school_user_id' => $schoolA->id,
                'full_name' => 'Siti Rahayu',
                'photo_profile_path' => User::DEFAULT_PROFILE_PHOTO_PATH,
                'major' => 'IPA',
                'school_origin' => 'SMA Negeri 1 Bogor',
                'graduation_status' => 'active',
                'class_year' => '2025',
                'unique_code' => 'SITI0001234X9YZ',
                'description' => 'Siswa aktif dalam kegiatan akademik dan organisasi.',
                'socials' => [
                    'website' => null,
                    'instagram' => null,
                    'linkedin' => null,
                    'whatsapp' => '081987654321',
                ],
                'address' => 'Bogor, Jawa Barat',
            ]
        );

        $studentB1 = User::query()->updateOrCreate(
            ['email' => 'student.b1@kompeta.test'],
            [
                'name' => 'Ahmad Wijaya',
                'password' => 'password123',
                'role' => User::ROLE_SISWA,
                'account_status' => User::STATUS_ACTIVE,
            ]
        );

        StudentProfile::query()->updateOrCreate(
            ['nisn' => '0002345678'],
            [
                'user_id' => $studentB1->id,
                'school_user_id' => $schoolB->id,
                'full_name' => 'Ahmad Wijaya',
                'photo_profile_path' => User::DEFAULT_PROFILE_PHOTO_PATH,
                'major' => 'IPS',
                'school_origin' => 'SMA Negeri 2 Jakarta',
                'graduation_status' => 'graduated',
                'class_year' => '2024',
                'unique_code' => 'qzPSGd04erQblY8G',
                'description' => 'Lulusan terbaik dengan minat karier operasional bisnis.',
                'socials' => [
                    'website' => null,
                    'instagram' => null,
                    'linkedin' => 'https://linkedin.com/in/ahmadwijaya',
                    'whatsapp' => '083456789012',
                ],
                'address' => 'Jakarta, DKI Jakarta',
            ]
        );
    }

    private function seedBulkSchools(int $count = 10): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $index = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $schoolName = "SMK Mitra Karya {$index}";

            $school = User::query()->updateOrCreate(
                ['email' => "school.bulk.{$index}@kompeta.test"],
                [
                    'name' => $schoolName,
                    'password' => 'password123',
                    'role' => User::ROLE_SEKOLAH,
                    'account_status' => User::STATUS_ACTIVE,
                ]
            );

            SchoolProfile::query()->updateOrCreate(
                ['user_id' => $school->id],
                [
                    'school_name' => $schoolName,
                    'npsn' => '31'.str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                    'accreditation' => $i % 3 === 0 ? 'B' : 'A',
                    'address' => "Jl. Pendidikan No. {$i}, Kota Contoh",
                    'socials' => SchoolProfile::DEFAULT_SOCIALS,
                    'expertise_fields' => ['TKJ', 'RPL', 'AKL'],
                    'logo_path' => "https://via.placeholder.com/200x200?text=SMK+Bulk+{$index}",
                    'image_1_path' => "https://via.placeholder.com/400x300?text=SMK+Bulk+{$index}+1",
                    'image_2_path' => "https://via.placeholder.com/400x300?text=SMK+Bulk+{$index}+2",
                    'image_3_path' => null,
                    'image_4_path' => null,
                    'image_5_path' => null,
                    'short_description' => "Sekolah dummy {$schoolName} untuk kebutuhan pengujian data eksplorasi.",
                    'operational_license_path' => $this->storeLocalFile(
                        "profiles/schools/legalities/smk-bulk-{$index}-license.pdf",
                        "Izin Operasional {$schoolName}"
                    ),
                ]
            );
        }
    }

    private function seedBulkMitra(int $total = 10): void
    {
        $companyCount = intdiv($total, 2);
        $umkmCount = $total - $companyCount;

        for ($i = 1; $i <= $companyCount; $i++) {
            $index = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $companyName = "PT Mitra Industri {$index}";

            $company = User::query()->updateOrCreate(
                ['email' => "mitra.bulk.company.{$index}@kompeta.test"],
                [
                    'name' => $companyName,
                    'password' => 'password123',
                    'role' => User::ROLE_MITRA,
                    'mitra_type' => User::MITRA_PERUSAHAAN,
                    'account_status' => User::STATUS_ACTIVE,
                ]
            );

            CompanyProfile::query()->updateOrCreate(
                ['user_id' => $company->id],
                [
                    'company_name' => $companyName,
                    'nib' => '777'.str_pad((string) $i, 10, '0', STR_PAD_LEFT),
                    'industry_sector' => $i % 2 === 0 ? 'Manufaktur' : 'Teknologi Informasi',
                    'employee_total_range' => $i % 2 === 0 ? '51-200' : '201-500',
                    'office_address' => "Kawasan Industri Blok {$index}, Kota Contoh",
                    'socials' => [
                        'website' => "https://example.com/mitra-industri-{$index}",
                        'instagram' => null,
                        'linkedin' => null,
                        'whatsapp' => null,
                    ],
                    'short_description' => "Perusahaan dummy {$companyName} untuk simulasi mitra tipe perusahaan.",
                    'company_logo_path' => "https://via.placeholder.com/200x200?text=Mitra+Industri+{$index}",
                    'image_1_path' => "https://via.placeholder.com/400x300?text=Mitra+Industri+{$index}+1",
                    'image_2_path' => "https://via.placeholder.com/400x300?text=Mitra+Industri+{$index}+2",
                    'image_3_path' => null,
                    'image_4_path' => null,
                    'image_5_path' => null,
                    'kemenkumham_decree_path' => $this->storeLocalFile(
                        "profiles/companies/legalities/mitra-industri-{$index}-sk.pdf",
                        "SK Kemenkumham {$companyName}"
                    ),
                ]
            );
        }

        for ($i = 1; $i <= $umkmCount; $i++) {
            $index = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $businessName = "UMKM Kreatif Nusantara {$index}";

            $umkm = User::query()->updateOrCreate(
                ['email' => "mitra.bulk.umkm.{$index}@kompeta.test"],
                [
                    'name' => $businessName,
                    'password' => 'password123',
                    'role' => User::ROLE_MITRA,
                    'mitra_type' => User::MITRA_UMKM,
                    'account_status' => User::STATUS_ACTIVE,
                ]
            );

            UmkmProfile::query()->updateOrCreate(
                ['user_id' => $umkm->id],
                [
                    'business_name' => $businessName,
                    'owner_nik' => '3301'.str_pad((string) $i, 12, '0', STR_PAD_LEFT),
                    'owner_personal_nib' => "NIB-UMKM-BULK-{$index}",
                    'business_type' => $i % 2 === 0 ? 'Kuliner' : 'Kerajinan',
                    'business_address' => "Sentra UMKM No. {$i}, Kota Contoh",
                    'socials' => UmkmProfile::DEFAULT_SOCIALS,
                    'umkm_logo_path' => "https://via.placeholder.com/200x200?text=UMKM+Bulk+{$index}",
                    'owner_ktp_photo_path' => $this->storeLocalFile(
                        "profiles/umkm/ktp/umkm-bulk-{$index}-ktp.jpg",
                        "KTP Owner {$businessName}"
                    ),
                    'short_description' => "UMKM dummy {$businessName} untuk simulasi mitra tipe UMKM.",
                    'image_1_path' => "https://via.placeholder.com/400x300?text=UMKM+Bulk+{$index}+1",
                    'image_2_path' => "https://via.placeholder.com/400x300?text=UMKM+Bulk+{$index}+2",
                    'image_3_path' => "https://via.placeholder.com/400x300?text=UMKM+Bulk+{$index}+3",
                    'image_4_path' => "https://via.placeholder.com/400x300?text=UMKM+Bulk+{$index}+4",
                    'image_5_path' => "https://via.placeholder.com/400x300?text=UMKM+Bulk+{$index}+5",
                ]
            );
        }
    }

    private function seedLargeSchoolWithStudents(int $totalStudents = 200): void
    {
        $school = User::query()->updateOrCreate(
            ['email' => 'school.massive@kompeta.test'],
            [
                'name' => 'SMK Pusat Talenta Nusantara',
                'password' => 'password123',
                'role' => User::ROLE_SEKOLAH,
                'account_status' => User::STATUS_ACTIVE,
            ]
        );

        SchoolProfile::query()->updateOrCreate(
            ['user_id' => $school->id],
            [
                'school_name' => 'SMK Pusat Talenta Nusantara',
                'npsn' => '31999999',
                'accreditation' => 'A',
                'address' => 'Jl. Pendidikan Nasional No. 200, Kota Contoh',
                'socials' => SchoolProfile::DEFAULT_SOCIALS,
                'expertise_fields' => ['RPL', 'TKJ', 'AKL', 'DKV'],
                'logo_path' => 'https://via.placeholder.com/200x200?text=SMK+Talenta',
                'image_1_path' => 'https://via.placeholder.com/400x300?text=SMK+Talenta+1',
                'image_2_path' => 'https://via.placeholder.com/400x300?text=SMK+Talenta+2',
                'image_3_path' => 'https://via.placeholder.com/400x300?text=SMK+Talenta+3',
                'image_4_path' => null,
                'image_5_path' => null,
                'short_description' => 'Sekolah dummy skala besar untuk simulasi data 200 siswa lintas status kelulusan.',
                'operational_license_path' => $this->storeLocalFile(
                    'profiles/schools/legalities/smk-talenta-license.pdf',
                    'Izin Operasional SMK Pusat Talenta Nusantara'
                ),
            ]
        );

        for ($i = 1; $i <= $totalStudents; $i++) {
            $index = str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            $fullName = "Siswa Massal {$index}";
            $graduationStatus = $i <= intdiv($totalStudents, 2) ? 'graduated' : 'active';

            $studentUser = User::query()->updateOrCreate(
                ['email' => "student.massive.{$index}@kompeta.test"],
                [
                    'name' => $fullName,
                    'password' => 'password123',
                    'role' => User::ROLE_SISWA,
                    'account_status' => User::STATUS_ACTIVE,
                ]
            );

            StudentProfile::query()->updateOrCreate(
                ['nisn' => '9'.str_pad((string) $i, 9, '0', STR_PAD_LEFT)],
                [
                    'user_id' => $studentUser->id,
                    'school_user_id' => $school->id,
                    'full_name' => $fullName,
                    'photo_profile_path' => User::DEFAULT_PROFILE_PHOTO_PATH,
                    'major' => $i % 3 === 0 ? 'RPL' : ($i % 3 === 1 ? 'TKJ' : 'AKL'),
                    'school_origin' => 'SMK Pusat Talenta Nusantara',
                    'graduation_status' => $graduationStatus,
                    'class_year' => $graduationStatus === 'graduated' ? '2024' : '2026',
                    'unique_code' => 'MS'.str_pad((string) $i, 14, '0', STR_PAD_LEFT),
                    'description' => "Profil dummy {$fullName} untuk simulasi data siswa sekolah besar.",
                    'socials' => StudentProfile::DEFAULT_SOCIALS,
                    'address' => 'Kota Contoh, Indonesia',
                ]
            );
        }
    }

    private function storeLocalFile(string $path, string $content): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'pdf') {
            $binary = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj 2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj 3 0 obj<</Type/Page/MediaBox[0 0 612 792]>>endobj\nxref\n0 4\n0000000000 65535 f\n0000000009 00000 n\n0000000058 00000 n\n0000000115 00000 n\ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n190\n%%EOF";
        } else {
            // Minimal valid JPEG (1x1 pixel)
            $binary = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/xAAUAQEAAAAAAAAAAAAAAAAAAAAA/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8AJQAB/9k=');
        }

        Storage::disk('local')->put($path, $binary);

        return $path;
    }
}
