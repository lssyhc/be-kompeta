<?php

namespace Database\Seeders;

use App\Models\CompanyProfile;
use App\Models\JobVacancy;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ExploreDummySeeder extends Seeder
{
    public function run(): void
    {
        $mitraPerusahaanA = User::query()->updateOrCreate(
            ['email' => 'mitra.company.a@kompeta.test'],
            [
                'name' => 'PT Binajasa Sumber Sarana',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_MITRA,
                'mitra_type' => User::MITRA_PERUSAHAAN,
                'account_status' => 'active',
                'is_active' => true,
            ]
        );

        CompanyProfile::query()->updateOrCreate(
            ['user_id' => $mitraPerusahaanA->id],
            [
                'company_name' => 'PT Binajasa Sumber Sarana',
                'nib' => '1234567890123',
                'industry_sector' => 'Outsourcing dan layanan tenaga kerja',
                'employee_total_range' => '501-1000',
                'office_address' => 'Jakarta Selatan, DKI Jakarta',
                'website_or_social_url' => 'https://example.com/binajasa',
                'short_description' => 'Perusahaan jasa tenaga kerja untuk operasional bisnis dan layanan office support.',
                'company_logo_path' => 'https://via.placeholder.com/200x200?text=Binajasa',
                'image_1_path' => 'https://via.placeholder.com/400x300?text=Binajasa+1',
                'image_2_path' => 'https://via.placeholder.com/400x300?text=Binajasa+2',
                'image_3_path' => 'https://via.placeholder.com/400x300?text=Binajasa+3',
                'image_4_path' => 'https://via.placeholder.com/400x300?text=Binajasa+4',
                'image_5_path' => 'https://via.placeholder.com/400x300?text=Binajasa+5',
                'kemenkumham_decree_path' => 'https://via.placeholder.com/600x800?text=SK+Kemenkumham',
            ]
        );

        $mitraPerusahaanB = User::query()->updateOrCreate(
            ['email' => 'mitra.company.b@kompeta.test'],
            [
                'name' => 'PT Surya Data Nusantara',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_MITRA,
                'mitra_type' => User::MITRA_PERUSAHAAN,
                'account_status' => 'active',
                'is_active' => true,
            ]
        );

        CompanyProfile::query()->updateOrCreate(
            ['user_id' => $mitraPerusahaanB->id],
            [
                'company_name' => 'PT Surya Data Nusantara',
                'nib' => '1234567890124',
                'industry_sector' => 'Teknologi informasi',
                'employee_total_range' => '201-500',
                'office_address' => 'Bandung, Jawa Barat',
                'website_or_social_url' => 'https://example.com/surya-data',
                'short_description' => 'Mitra digitalisasi operasional dan pengolahan data untuk bisnis menengah.',
                'company_logo_path' => 'https://via.placeholder.com/200x200?text=Surya',
                'image_1_path' => 'https://via.placeholder.com/400x300?text=Surya+Data+1',
                'image_2_path' => 'https://via.placeholder.com/400x300?text=Surya+Data+2',
                'image_3_path' => 'https://via.placeholder.com/400x300?text=Surya+Data+3',
                'image_4_path' => 'https://via.placeholder.com/400x300?text=Surya+Data+4',
                'image_5_path' => 'https://via.placeholder.com/400x300?text=Surya+Data+5',
                'kemenkumham_decree_path' => 'https://via.placeholder.com/600x800?text=SK+Kemenkumham',
            ]
        );

        $mitraUmkmA = User::query()->updateOrCreate(
            ['email' => 'mitra.umkm.a@kompeta.test'],
            [
                'name' => 'Roti Bunda Rasa',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_MITRA,
                'mitra_type' => User::MITRA_UMKM,
                'account_status' => 'active',
                'is_active' => true,
            ]
        );

        UmkmProfile::query()->updateOrCreate(
            ['user_id' => $mitraUmkmA->id],
            [
                'business_name' => 'Roti Bunda Rasa',
                'owner_nik' => '3201234567891111',
                'owner_personal_nib' => 'NIB-UMKM-001',
                'business_type' => 'Kuliner',
                'business_address' => 'Semarang, Jawa Tengah',
                'umkm_logo_path' => 'https://via.placeholder.com/200x200?text=Roti+Bunda',
                'owner_ktp_photo_path' => 'https://via.placeholder.com/350x220?text=KTP+Owner',
                'short_description' => 'UMKM kuliner rumahan yang fokus pada bakery harian dan pemesanan acara.',
                'image_1_path' => 'https://via.placeholder.com/400x300?text=Roti+Bunda+1',
                'image_2_path' => 'https://via.placeholder.com/400x300?text=Roti+Bunda+2',
                'image_3_path' => 'https://via.placeholder.com/400x300?text=Roti+Bunda+3',
                'image_4_path' => 'https://via.placeholder.com/400x300?text=Roti+Bunda+4',
                'image_5_path' => 'https://via.placeholder.com/400x300?text=Roti+Bunda+5',
            ]
        );

        $mitraUmkmB = User::query()->updateOrCreate(
            ['email' => 'mitra.umkm.b@kompeta.test'],
            [
                'name' => 'Craft Kayu Jogja',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_MITRA,
                'mitra_type' => User::MITRA_UMKM,
                'account_status' => 'active',
                'is_active' => true,
            ]
        );

        UmkmProfile::query()->updateOrCreate(
            ['user_id' => $mitraUmkmB->id],
            [
                'business_name' => 'Craft Kayu Jogja',
                'owner_nik' => '3201234567892222',
                'owner_personal_nib' => 'NIB-UMKM-002',
                'business_type' => 'Kerajinan',
                'business_address' => 'Sleman, DI Yogyakarta',
                'umkm_logo_path' => 'https://via.placeholder.com/200x200?text=Craft+Kayu',
                'owner_ktp_photo_path' => 'https://via.placeholder.com/350x220?text=KTP+Owner',
                'short_description' => 'UMKM kerajinan kayu untuk kebutuhan dekorasi rumah, hotel, dan kantor.',
                'image_1_path' => 'https://via.placeholder.com/400x300?text=Craft+Kayu+Jogja+1',
                'image_2_path' => 'https://via.placeholder.com/400x300?text=Craft+Kayu+Jogja+2',
                'image_3_path' => 'https://via.placeholder.com/400x300?text=Craft+Kayu+Jogja+3',
                'image_4_path' => 'https://via.placeholder.com/400x300?text=Craft+Kayu+Jogja+4',
                'image_5_path' => 'https://via.placeholder.com/400x300?text=Craft+Kayu+Jogja+5',
            ]
        );

        $vacancies = [
            [
                'mitra_user_id' => $mitraPerusahaanA->id,
                'position_name' => 'Accounting Staff',
                'category' => 'Keuangan',
                'job_type' => JobVacancy::JOB_TYPE_FULL_TIME,
                'work_policy' => JobVacancy::WORK_POLICY_OFFICE,
                'experience_level' => JobVacancy::EXPERIENCE_LESS_THAN_ONE_YEAR,
                'province' => 'DKI Jakarta',
                'salary_min' => 5700000,
                'salary_max' => 5800000,
                'is_salary_hidden' => false,
                'requirements' => "Minimal Sarjana (S1).\nMemahami MYOB.\nKomunikasi baik.",
                'job_description' => 'Mengerjakan proses jurnal, rekonsiliasi, dan laporan keuangan harian perusahaan.',
                'created_at' => now()->subHours(5),
                'updated_at' => now()->subHours(5),
                'skills' => ['MYOB', 'Excel', 'Akuntansi Dasar'],
                'benefits' => ['BPJS Kesehatan', 'BPJS Ketenagakerjaan', 'Makan Siang'],
            ],
            [
                'mitra_user_id' => $mitraPerusahaanA->id,
                'position_name' => 'Security Freelance',
                'category' => 'Keamanan',
                'job_type' => JobVacancy::JOB_TYPE_FREELANCE,
                'work_policy' => JobVacancy::WORK_POLICY_OFFICE,
                'experience_level' => JobVacancy::EXPERIENCE_NO_EXPERIENCE,
                'province' => 'DKI Jakarta',
                'salary_min' => null,
                'salary_max' => null,
                'is_salary_hidden' => true,
                'requirements' => "Minimal SMA/SMK.\nBersedia kerja shift.",
                'job_description' => 'Menjaga keamanan area kantor dan melakukan patroli berkala.',
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
                'skills' => ['Office Security', 'Hotel Security'],
                'benefits' => ['Uang Makan', 'Seragam Kerja'],
            ],
            [
                'mitra_user_id' => $mitraPerusahaanB->id,
                'position_name' => 'Data Entry Operator',
                'category' => 'Administrasi',
                'job_type' => JobVacancy::JOB_TYPE_CONTRACT,
                'work_policy' => JobVacancy::WORK_POLICY_HYBRID,
                'experience_level' => JobVacancy::EXPERIENCE_FRESH_GRADUATE,
                'province' => 'Jawa Barat',
                'salary_min' => 4200000,
                'salary_max' => 5000000,
                'is_salary_hidden' => false,
                'requirements' => "Minimal D3 semua jurusan.\nTeliti dan cepat mengetik.",
                'job_description' => 'Input, validasi, dan sinkronisasi data operasional klien setiap hari.',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(1),
                'skills' => ['Data Entry', 'Google Sheets', 'Administrasi'],
                'benefits' => ['Asuransi Kesehatan', 'Laptop Kerja'],
            ],
            [
                'mitra_user_id' => $mitraPerusahaanB->id,
                'position_name' => 'Junior QA Tester',
                'category' => 'Teknologi',
                'job_type' => JobVacancy::JOB_TYPE_FULL_TIME,
                'work_policy' => JobVacancy::WORK_POLICY_REMOTE,
                'experience_level' => JobVacancy::EXPERIENCE_FRESH_GRADUATE,
                'province' => 'Jawa Barat',
                'salary_min' => 5000000,
                'salary_max' => 6500000,
                'is_salary_hidden' => false,
                'requirements' => "Mengerti dasar software testing.\nMampu membuat test case.",
                'job_description' => 'Menguji fitur aplikasi web dan mobile serta menuliskan bug report.',
                'created_at' => now()->subDays(8),
                'updated_at' => now()->subDays(4),
                'skills' => ['Manual Testing', 'Test Case Writing', 'Postman'],
                'benefits' => ['Remote Allowance', 'Learning Budget'],
            ],
            [
                'mitra_user_id' => $mitraUmkmA->id,
                'position_name' => 'Baker Assistant',
                'category' => 'Produksi',
                'job_type' => JobVacancy::JOB_TYPE_PART_TIME,
                'work_policy' => JobVacancy::WORK_POLICY_OFFICE,
                'experience_level' => JobVacancy::EXPERIENCE_NO_EXPERIENCE,
                'province' => 'Jawa Tengah',
                'salary_min' => 2200000,
                'salary_max' => 3000000,
                'is_salary_hidden' => false,
                'requirements' => "Menyukai pekerjaan dapur.\nSiap kerja pagi.",
                'job_description' => 'Membantu persiapan adonan, oven, dan kemasan produk roti harian.',
                'created_at' => now()->subHours(10),
                'updated_at' => now()->subHours(6),
                'skills' => ['Food Preparation', 'Packing'],
                'benefits' => ['Makan Harian', 'Bonus Penjualan'],
            ],
            [
                'mitra_user_id' => $mitraUmkmA->id,
                'position_name' => 'Content Creator Produk UMKM',
                'category' => 'Pemasaran',
                'job_type' => JobVacancy::JOB_TYPE_FREELANCE,
                'work_policy' => JobVacancy::WORK_POLICY_REMOTE,
                'experience_level' => JobVacancy::EXPERIENCE_LESS_THAN_ONE_YEAR,
                'province' => 'Jawa Tengah',
                'salary_min' => 1500000,
                'salary_max' => 3500000,
                'is_salary_hidden' => false,
                'requirements' => "Memiliki portofolio konten pendek.\nPaham trend media sosial.",
                'job_description' => 'Membuat konten foto/video produk untuk kebutuhan marketplace dan media sosial.',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
                'skills' => ['Copywriting', 'Canva', 'Video Editing'],
                'benefits' => ['Jam Kerja Fleksibel', 'Insentif KPI'],
            ],
            [
                'mitra_user_id' => $mitraUmkmB->id,
                'position_name' => 'Operator Produksi Kerajinan',
                'category' => 'Produksi',
                'job_type' => JobVacancy::JOB_TYPE_CONTRACT,
                'work_policy' => JobVacancy::WORK_POLICY_OFFICE,
                'experience_level' => JobVacancy::EXPERIENCE_NO_EXPERIENCE,
                'province' => 'DI Yogyakarta',
                'salary_min' => 2600000,
                'salary_max' => 3400000,
                'is_salary_hidden' => false,
                'requirements' => "Mampu menggunakan alat kerja sederhana.\nTelaten dan rapi.",
                'job_description' => 'Mengolah material kayu, finishing produk, dan quality check sebelum pengiriman.',
                'created_at' => now()->subDays(6),
                'updated_at' => now()->subDays(5),
                'skills' => ['Woodworking', 'Finishing'],
                'benefits' => ['Transport Allowance', 'Lembur Berbayar'],
            ],
            [
                'mitra_user_id' => $mitraUmkmB->id,
                'position_name' => 'Magang Admin Marketplace',
                'category' => 'Administrasi',
                'job_type' => JobVacancy::JOB_TYPE_INTERNSHIP,
                'work_policy' => JobVacancy::WORK_POLICY_HYBRID,
                'experience_level' => JobVacancy::EXPERIENCE_FRESH_GRADUATE,
                'province' => 'DI Yogyakarta',
                'salary_min' => 1000000,
                'salary_max' => 1500000,
                'is_salary_hidden' => false,
                'requirements' => "Minimal SMA/SMK.\nPaham dasar marketplace.",
                'job_description' => 'Membantu upload produk, optimasi deskripsi, dan respon chat pembeli.',
                'created_at' => now()->subDays(12),
                'updated_at' => now()->subDays(10),
                'skills' => ['Marketplace Management', 'Customer Service'],
                'benefits' => ['Sertifikat Magang', 'Mentoring'],
            ],
        ];

        foreach ($vacancies as $item) {
            $slug = Str::slug($item['position_name']).'-'.substr(md5($item['mitra_user_id'].$item['position_name']), 0, 6);

            $vacancy = JobVacancy::query()->updateOrCreate(
                [
                    'slug' => $slug,
                ],
                [
                    'mitra_user_id' => $item['mitra_user_id'],
                    'position_name' => $item['position_name'],
                    'category' => $item['category'],
                    'job_type' => $item['job_type'],
                    'work_policy' => $item['work_policy'],
                    'experience_level' => $item['experience_level'],
                    'province' => $item['province'],
                    'salary_min' => $item['salary_min'],
                    'salary_max' => $item['salary_max'],
                    'is_salary_hidden' => $item['is_salary_hidden'],
                    'requirements' => $item['requirements'],
                    'job_description' => $item['job_description'],
                    'is_published' => true,
                    'created_at' => $item['created_at'],
                    'updated_at' => $item['updated_at'],
                ]
            );

            $vacancy->skills()->delete();
            $vacancy->benefits()->delete();

            foreach ($item['skills'] as $skill) {
                $vacancy->skills()->create(['name' => $skill]);
            }

            foreach ($item['benefits'] as $benefit) {
                $vacancy->benefits()->create(['name' => $benefit]);
            }
        }
    }
}
