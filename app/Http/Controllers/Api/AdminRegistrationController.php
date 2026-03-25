<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AccountApprovedMail;
use App\Mail\AccountRejectedMail;
use App\Models\CompanyProfile;
use App\Models\SchoolProfile;
use App\Models\UmkmProfile;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AdminRegistrationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $admin = $request->user();

        if (! $admin instanceof User) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        if ($admin->role !== User::ROLE_ADMIN) {
            return $this->errorResponse('Hanya admin yang dapat mengakses data registrasi.', 403);
        }

        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:pending,active'],
            'role' => ['nullable', 'string', 'in:sekolah,mitra'],
            'mitra_type' => ['nullable', 'string', 'in:perusahaan,umkm'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $status = (string) ($validated['status'] ?? User::STATUS_PENDING);
        $perPage = (int) ($validated['per_page'] ?? 10);

        $query = User::query()
            ->whereIn('role', [User::ROLE_SEKOLAH, User::ROLE_MITRA])
            ->where('account_status', $status)
            ->with(['schoolProfile', 'companyProfile', 'umkmProfile'])
            ->orderByDesc('created_at');

        if (isset($validated['role'])) {
            $query->where('role', $validated['role']);
        }

        if (isset($validated['mitra_type'])) {
            $query->where('mitra_type', $validated['mitra_type']);
        }

        /** @var LengthAwarePaginator<int, mixed> $paginator */
        $paginator = $query
            ->paginate($perPage)
            ->appends($request->query());

        $paginator->setCollection(
            $paginator->getCollection()->values()->map(
                fn (User $user, int $index): array => $this->transformRegistrationItem($user)
            )
        );

        return $this->paginatedResponse($paginator, 'Daftar registrasi berhasil diambil.');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $admin = $request->user();

        if (! $admin instanceof User) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        if ($admin->role !== User::ROLE_ADMIN) {
            return $this->errorResponse('Hanya admin yang dapat mengakses data registrasi.', 403);
        }

        $user = $this->findRegistrationUser($id);

        if (! $user instanceof User) {
            return $this->errorResponse('Data registrasi tidak ditemukan.', 404);
        }

        return $this->successResponse(
            $this->transformRegistrationItem($user),
            'Detail registrasi berhasil diambil.'
        );
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $admin = $request->user();

        if (! $admin instanceof User) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        if ($admin->role !== User::ROLE_ADMIN) {
            return $this->errorResponse('Hanya admin yang dapat menyetujui registrasi.', 403);
        }

        $user = $this->findRegistrationUser($id);

        if (! $user instanceof User) {
            return $this->errorResponse('Data registrasi tidak ditemukan.', 404);
        }

        if ($user->account_status !== User::STATUS_PENDING) {
            return $this->errorResponse('Registrasi ini tidak dalam status pending.', 422);
        }

        $user->forceFill([
            'account_status' => User::STATUS_ACTIVE,
        ])->save();

        try {
            Mail::to($user->email)->send(new AccountApprovedMail($user));
        } catch (Exception $e) {
            Log::error('Gagal mengirim email approval akun', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $this->successResponse(
            $this->transformRegistrationItem($user->fresh(['schoolProfile', 'companyProfile', 'umkmProfile'])),
            'Registrasi berhasil disetujui.'
        );
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $admin = $request->user();

        if (! $admin instanceof User) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        if ($admin->role !== User::ROLE_ADMIN) {
            return $this->errorResponse('Hanya admin yang dapat menolak registrasi.', 403);
        }

        $user = $this->findRegistrationUser($id);

        if (! $user instanceof User) {
            return $this->errorResponse('Data registrasi tidak ditemukan.', 404);
        }

        if ($user->account_status !== User::STATUS_PENDING) {
            return $this->errorResponse('Registrasi ini tidak dalam status pending.', 422);
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $responseData = $this->transformRegistrationItem($user);

        try {
            Mail::to($user->email)->send(new AccountRejectedMail($user, $validated['reason'] ?? null));
        } catch (Exception $e) {
            Log::error('Gagal mengirim email penolakan (reject) akun', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        DB::transaction(function () use ($user): void {
            $this->deleteUploadedFiles($user);

            $user->schoolProfile()->delete();
            $user->companyProfile()->delete();
            $user->umkmProfile()->delete();

            $user->tokens()->delete();
            $user->delete();
        });

        return $this->successResponse($responseData, 'Registrasi berhasil ditolak dan data akun telah dihapus.');
    }

    private function findRegistrationUser(int $id): ?User
    {
        return User::query()
            ->whereIn('role', [User::ROLE_SEKOLAH, User::ROLE_MITRA])
            ->where('id', $id)
            ->with(['schoolProfile', 'companyProfile', 'umkmProfile'])
            ->first();
    }

    private function deleteUploadedFiles(User $user): void
    {
        $publicPaths = [];
        $localPaths = [];

        $schoolProfile = $user->schoolProfile;

        if ($schoolProfile instanceof SchoolProfile) {
            $publicPaths = array_merge($publicPaths, array_filter([
                $schoolProfile->logo_path,
                $schoolProfile->image_1_path,
                $schoolProfile->image_2_path,
                $schoolProfile->image_3_path,
                $schoolProfile->image_4_path,
                $schoolProfile->image_5_path,
            ]));
            $localPaths = array_merge($localPaths, array_filter([
                $schoolProfile->operational_license_path,
            ]));
        }

        $companyProfile = $user->companyProfile;

        if ($companyProfile instanceof CompanyProfile) {
            $publicPaths = array_merge($publicPaths, array_filter([
                $companyProfile->company_logo_path,
                $companyProfile->image_1_path,
                $companyProfile->image_2_path,
                $companyProfile->image_3_path,
                $companyProfile->image_4_path,
                $companyProfile->image_5_path,
            ]));
            $localPaths = array_merge($localPaths, array_filter([
                $companyProfile->kemenkumham_decree_path,
            ]));
        }

        $umkmProfile = $user->umkmProfile;

        if ($umkmProfile instanceof UmkmProfile) {
            $publicPaths = array_merge($publicPaths, array_filter([
                $umkmProfile->umkm_logo_path,
                $umkmProfile->image_1_path,
                $umkmProfile->image_2_path,
                $umkmProfile->image_3_path,
                $umkmProfile->image_4_path,
                $umkmProfile->image_5_path,
            ]));
            $localPaths = array_merge($localPaths, array_filter([
                $umkmProfile->owner_ktp_photo_path,
            ]));
        }

        foreach ($publicPaths as $path) {
            if (! str_starts_with($path, 'http') && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        foreach ($localPaths as $path) {
            if (Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }
        }
    }

    private function transformRegistrationItem(User $user): array
    {
        return [
            'id' => $user->id,
            'nama' => $user->name,
            'title' => $this->resolveTitle($user),
            'status' => $user->account_status,
            'detail' => $this->resolveDetail($user),
        ];
    }

    private function resolveTitle(User $user): string
    {
        if ($user->role === User::ROLE_SEKOLAH) {
            return 'Sekolah';
        }

        if ($user->mitra_type === User::MITRA_PERUSAHAAN) {
            return 'Mitra Perusahaan';
        }

        if ($user->mitra_type === User::MITRA_UMKM) {
            return 'Mitra UMKM';
        }

        return 'Mitra';
    }

    private function resolveDetail(User $user): array
    {
        $schoolProfile = $user->schoolProfile;

        if ($user->role === User::ROLE_SEKOLAH && $schoolProfile instanceof SchoolProfile) {
            return [
                'email' => $user->email,
                'school_name' => $schoolProfile->school_name,
                'npsn' => $schoolProfile->npsn,
                'accreditation' => $schoolProfile->accreditation,
                'address' => $schoolProfile->address,
                'short_description' => $schoolProfile->short_description,
                'operational_license_url' => url("/api/admin/registrations/{$user->id}/documents/school-license"),
            ];
        }

        $companyProfile = $user->companyProfile;

        if ($user->mitra_type === User::MITRA_PERUSAHAAN && $companyProfile instanceof CompanyProfile) {
            return [
                'email' => $user->email,
                'company_name' => $companyProfile->company_name,
                'nib' => $companyProfile->nib,
                'industry_sector' => $companyProfile->industry_sector,
                'office_address' => $companyProfile->office_address,
                'short_description' => $companyProfile->short_description,
                'kemenkumham_decree_url' => url("/api/admin/registrations/{$user->id}/documents/company-decree"),
            ];
        }

        $umkmProfile = $user->umkmProfile;

        if ($user->mitra_type === User::MITRA_UMKM && $umkmProfile instanceof UmkmProfile) {
            return [
                'email' => $user->email,
                'business_name' => $umkmProfile->business_name,
                'owner_nik' => $umkmProfile->owner_nik,
                'business_type' => $umkmProfile->business_type,
                'business_address' => $umkmProfile->business_address,
                'short_description' => $umkmProfile->short_description,
                'owner_ktp_photo_url' => url("/api/admin/registrations/{$user->id}/documents/umkm-ktp"),
            ];
        }

        return [
            'email' => $user->email,
            'role' => $user->role,
            'mitra_type' => $user->mitra_type,
        ];
    }

    public function downloadDocument(Request $request, int $id, string $type): mixed
    {
        $admin = $request->user();

        if (! $admin instanceof User || $admin->role !== User::ROLE_ADMIN) {
            return $this->errorResponse('Hanya admin yang dapat mengakses dokumen.', 403);
        }

        $user = $this->findRegistrationUser($id);

        if (! $user instanceof User) {
            return $this->errorResponse('Data registrasi tidak ditemukan.', 404);
        }

        $schoolProfile = $user->schoolProfile;
        $companyProfile = $user->companyProfile;
        $umkmProfile = $user->umkmProfile;

        $path = match ($type) {
            'school-license' => $schoolProfile instanceof SchoolProfile ? $schoolProfile->operational_license_path : null,
            'company-decree' => $companyProfile instanceof CompanyProfile ? $companyProfile->kemenkumham_decree_path : null,
            'umkm-ktp' => $umkmProfile instanceof UmkmProfile ? $umkmProfile->owner_ktp_photo_path : null,
            default => null,
        };

        if (! is_string($path) || $path === '' || ! Storage::disk('local')->exists($path)) {
            return $this->errorResponse('Dokumen tidak ditemukan.', 404);
        }

        try {
            return Storage::disk('local')->download($path);
        } catch (Exception $e) {
            Log::error('Gagal mengunduh dokumen registrasi', [
                'user_id' => $id,
                'type' => $type,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse('Gagal mengunduh dokumen.', 500);
        }
    }
}
