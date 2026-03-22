<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\SchoolProfile;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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
            'status' => ['nullable', 'string', 'in:pending,active,rejected'],
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

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query
            ->paginate($perPage)
            ->appends($request->query());

        $startNo = ($paginator->currentPage() - 1) * $paginator->perPage();

        $paginator->setCollection(
            $paginator->getCollection()->values()->map(
                fn (User $user, int $index): array => $this->transformRegistrationItem($user, $startNo + $index + 1)
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
            $this->transformRegistrationItem($user, $user->id),
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
            'is_active' => true,
        ])->save();

        return $this->successResponse(
            $this->transformRegistrationItem($user->fresh(['schoolProfile', 'companyProfile', 'umkmProfile']), $user->id),
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

        $user->forceFill([
            'account_status' => User::STATUS_REJECTED,
            'is_active' => false,
        ])->save();

        return $this->successResponse(
            $this->transformRegistrationItem($user->fresh(['schoolProfile', 'companyProfile', 'umkmProfile']), $user->id),
            'Registrasi berhasil ditolak.'
        );
    }

    private function findRegistrationUser(int $id): ?User
    {
        return User::query()
            ->whereIn('role', [User::ROLE_SEKOLAH, User::ROLE_MITRA])
            ->where('id', $id)
            ->with(['schoolProfile', 'companyProfile', 'umkmProfile'])
            ->first();
    }

    private function transformRegistrationItem(User $user, int $no): array
    {
        return [
            'id' => $user->id,
            'no' => $no,
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
            ];
        }

        return [
            'email' => $user->email,
            'role' => $user->role,
            'mitra_type' => $user->mitra_type,
        ];
    }
}
