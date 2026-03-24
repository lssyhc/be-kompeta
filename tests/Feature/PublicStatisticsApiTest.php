<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicStatisticsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_summary_returns_active_counts_by_role_and_mitra_type(): void
    {
        User::factory()->count(3)->create([
            'role' => User::ROLE_SISWA,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        User::factory()->count(2)->create([
            'role' => User::ROLE_SEKOLAH,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        User::factory()->count(4)->create([
            'role' => User::ROLE_MITRA,
            'mitra_type' => User::MITRA_PERUSAHAAN,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        User::factory()->count(5)->create([
            'role' => User::ROLE_MITRA,
            'mitra_type' => User::MITRA_UMKM,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        User::factory()->create([
            'role' => User::ROLE_SISWA,
            'account_status' => User::STATUS_PENDING,
        ]);

        User::factory()->create([
            'role' => User::ROLE_MITRA,
            'mitra_type' => User::MITRA_PERUSAHAAN,
            'account_status' => User::STATUS_PENDING,
        ]);

        $response = $this->getJson('/api/public/summary');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.student_count', 3)
            ->assertJsonPath('data.school_count', 2)
            ->assertJsonPath('data.company_mitra_count', 4)
            ->assertJsonPath('data.umkm_mitra_count', 5);
    }
}
