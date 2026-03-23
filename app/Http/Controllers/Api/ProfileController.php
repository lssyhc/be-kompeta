<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\AdminProfile;
use App\Models\CompanyProfile;
use App\Models\SchoolProfile;
use App\Models\StudentApplication;
use App\Models\StudentProfile;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        return $this->successResponse([
            'user' => $this->compactUser($user),
            'role_profile' => $this->resolveRoleProfile($user),
        ], 'Profil berhasil diambil.');
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $validated = $request->validated();
        $userPayload = $validated['user'] ?? [];

        if (! empty($userPayload)) {
            $user->update($userPayload);
        }

        $profile = $this->resolveProfileModel($user, true);

        if (! $profile instanceof Model) {
            return $this->errorResponse('Profil untuk role ini belum tersedia.', 422);
        }

        $profilePayload = $validated['profile'] ?? [];

        if ($user->role === User::ROLE_SISWA) {
            $legacyStudentPayload = array_intersect_key($validated, array_flip([
                'description',
                'phone_number',
                'address',
                'class_year',
            ]));

            $profilePayload = array_merge($legacyStudentPayload, $profilePayload);
        }

        if (! empty($profilePayload)) {
            $profile->update($profilePayload);
        }

        return $this->successResponse([
            'user' => $this->compactUser($user),
            'role_profile' => $this->resolveRoleProfile($user),
        ], 'Profil berhasil diperbarui.');
    }

    private function compactUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'mitra_type' => $user->mitra_type,
            'account_status' => $user->account_status,
            'is_active' => $user->is_active,
            'last_login_at' => $user->last_login_at,
        ];
    }

    private function resolveRoleProfile(User $user): mixed
    {
        if ($user->role === User::ROLE_SISWA) {
            $studentProfile = StudentProfile::query()
                ->where('user_id', $user->id)
                ->with(['skills', 'experiences', 'achievements', 'applications.jobVacancy'])
                ->first();

            if (! $studentProfile instanceof StudentProfile) {
                return null;
            }

            $profilePayload = $studentProfile->toArray();

            /** @var Collection<int, StudentApplication> $applications */
            $applications = $studentProfile->applications;
            $profilePayload['job_applications'] = $applications
                ->map(function (StudentApplication $application): array {
                    return [
                        'company_name' => $application->company_name,
                        'role_type' => $application->role_type,
                        'submitted_at' => $application->submitted_at?->toDateString(),
                        'status' => $application->status ?: $application->submit_status,
                    ];
                })
                ->values()
                ->all();

            return $profilePayload;
        }

        if ($user->role === User::ROLE_SEKOLAH) {
            return SchoolProfile::query()->where('user_id', $user->id)->first();
        }

        if ($user->role === User::ROLE_MITRA && $user->mitra_type === User::MITRA_PERUSAHAAN) {
            return CompanyProfile::query()->where('user_id', $user->id)->first();
        }

        if ($user->role === User::ROLE_MITRA && $user->mitra_type === User::MITRA_UMKM) {
            return UmkmProfile::query()->where('user_id', $user->id)->first();
        }

        if ($user->role === User::ROLE_ADMIN) {
            return AdminProfile::query()->where('user_id', $user->id)->first();
        }

        return null;
    }

    private function resolveProfileModel(User $user, bool $ensureAdminProfile = false): ?Model
    {
        if ($user->role === User::ROLE_SISWA) {
            return StudentProfile::query()->where('user_id', $user->id)->first();
        }

        if ($user->role === User::ROLE_SEKOLAH) {
            return SchoolProfile::query()->where('user_id', $user->id)->first();
        }

        if ($user->role === User::ROLE_MITRA && $user->mitra_type === User::MITRA_PERUSAHAAN) {
            return CompanyProfile::query()->where('user_id', $user->id)->first();
        }

        if ($user->role === User::ROLE_MITRA && $user->mitra_type === User::MITRA_UMKM) {
            return UmkmProfile::query()->where('user_id', $user->id)->first();
        }

        if ($user->role === User::ROLE_ADMIN) {
            if (! $ensureAdminProfile) {
                return AdminProfile::query()->where('user_id', $user->id)->first();
            }

            return AdminProfile::query()->firstOrCreate(
                ['user_id' => $user->id],
                ['full_name' => $user->name]
            );
        }

        return null;
    }
}
