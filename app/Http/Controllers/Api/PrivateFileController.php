<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\SchoolProfile;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrivateFileController extends Controller
{
    public function downloadMyDocument(Request $request, string $type): JsonResponse|StreamedResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $path = match (true) {
            $user->role === User::ROLE_SEKOLAH && $type === 'operational_license' => SchoolProfile::query()
                ->where('user_id', $user->id)
                ->value('operational_license_path'),
            $user->role === User::ROLE_MITRA && $user->mitra_type === User::MITRA_PERUSAHAAN && $type === 'kemenkumham_decree' => CompanyProfile::query()
                ->where('user_id', $user->id)
                ->value('kemenkumham_decree_path'),
            $user->role === User::ROLE_MITRA && $user->mitra_type === User::MITRA_UMKM && $type === 'owner_ktp' => UmkmProfile::query()
                ->where('user_id', $user->id)
                ->value('owner_ktp_photo_path'),
            default => null,
        };

        if (! $path) {
            return $this->errorResponse('Dokumen tidak ditemukan.', 404);
        }

        if (! Storage::disk('local')->exists($path)) {
            return $this->errorResponse('File tidak ditemukan pada storage.', 404);
        }

        return Storage::disk('local')->download($path);
    }
}
