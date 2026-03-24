<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class PublicStatisticsController extends Controller
{
    public function summary(): JsonResponse
    {
        $activeUsers = User::query()
            ->where('account_status', User::STATUS_ACTIVE)
            ->where('is_active', true);

        $data = [
            'student_count' => (clone $activeUsers)->where('role', User::ROLE_SISWA)->count(),
            'school_count' => (clone $activeUsers)->where('role', User::ROLE_SEKOLAH)->count(),
            'company_mitra_count' => (clone $activeUsers)
                ->where('role', User::ROLE_MITRA)
                ->where('mitra_type', User::MITRA_PERUSAHAAN)
                ->count(),
            'umkm_mitra_count' => (clone $activeUsers)
                ->where('role', User::ROLE_MITRA)
                ->where('mitra_type', User::MITRA_UMKM)
                ->count(),
        ];

        return $this->successResponse($data, 'Statistik publik berhasil diambil.');
    }
}
