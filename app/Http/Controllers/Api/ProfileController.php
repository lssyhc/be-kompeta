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
use Illuminate\Support\Facades\Storage;

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
        $filePayload = $this->handleFileUploads($request, $user, $profile);
        $profilePayload = array_merge($profilePayload, $filePayload);

        if (! empty($profilePayload)) {
            $profile->update($profilePayload);
        }

        return $this->successResponse([
            'user' => $this->compactUser($user),
            'role_profile' => $this->resolveRoleProfile($user),
        ], 'Profil berhasil diperbarui.');
    }

    private function handleFileUploads(UpdateProfileRequest $request, User $user, Model $profile): array
    {
        $filePayload = [];

        if ($user->role === User::ROLE_SISWA) {
            /** @var StudentProfile $profile */
            if ($request->hasFile('photo_profile')) {
                $this->deleteOldFile($profile->photo_profile_path, 'public');
                $filePayload['photo_profile_path'] = $request->file('photo_profile')->store('profiles/students/photos', 'public');
            }
        }

        if ($user->role === User::ROLE_SEKOLAH) {
            /** @var SchoolProfile $profile */
            if ($request->hasFile('logo')) {
                $this->deleteOldFile($profile->logo_path, 'public');
                $filePayload['logo_path'] = $request->file('logo')->store('profiles/schools/logos', 'public');
            }
            if ($request->hasFile('operational_license')) {
                $this->deleteOldFile($profile->operational_license_path, 'local');
                $filePayload['operational_license_path'] = $request->file('operational_license')->store('profiles/schools/legalities', 'local');
            }
            foreach (range(1, 5) as $i) {
                if ($request->hasFile("image_{$i}")) {
                    $this->deleteOldFile($profile->{"image_{$i}_path"}, 'public');
                    $filePayload["image_{$i}_path"] = $request->file("image_{$i}")->store('profiles/schools/images', 'public');
                }
            }
        }

        if ($user->role === User::ROLE_MITRA && $user->mitra_type === User::MITRA_PERUSAHAAN) {
            /** @var CompanyProfile $profile */
            if ($request->hasFile('company_logo')) {
                $this->deleteOldFile($profile->company_logo_path, 'public');
                $filePayload['company_logo_path'] = $request->file('company_logo')->store('profiles/companies/logos', 'public');
            }
            if ($request->hasFile('kemenkumham_decree')) {
                $this->deleteOldFile($profile->kemenkumham_decree_path, 'local');
                $filePayload['kemenkumham_decree_path'] = $request->file('kemenkumham_decree')->store('profiles/companies/legalities', 'local');
            }
            foreach (range(1, 5) as $i) {
                if ($request->hasFile("image_{$i}")) {
                    $this->deleteOldFile($profile->{"image_{$i}_path"}, 'public');
                    $filePayload["image_{$i}_path"] = $request->file("image_{$i}")->store('profiles/companies/images', 'public');
                }
            }
        }

        if ($user->role === User::ROLE_MITRA && $user->mitra_type === User::MITRA_UMKM) {
            /** @var UmkmProfile $profile */
            if ($request->hasFile('umkm_logo')) {
                $this->deleteOldFile($profile->umkm_logo_path, 'public');
                $filePayload['umkm_logo_path'] = $request->file('umkm_logo')->store('profiles/umkm/logos', 'public');
            }
            if ($request->hasFile('owner_ktp_photo')) {
                $this->deleteOldFile($profile->owner_ktp_photo_path, 'local');
                $filePayload['owner_ktp_photo_path'] = $request->file('owner_ktp_photo')->store('profiles/umkm/ktp', 'local');
            }
            foreach (range(1, 5) as $i) {
                if ($request->hasFile("image_{$i}")) {
                    $this->deleteOldFile($profile->{"image_{$i}_path"}, 'public');
                    $filePayload["image_{$i}_path"] = $request->file("image_{$i}")->store('profiles/umkm/images', 'public');
                }
            }
        }

        if ($user->role === User::ROLE_ADMIN) {
            /** @var AdminProfile $profile */
            if ($request->hasFile('avatar')) {
                $this->deleteOldFile($profile->avatar_path, 'public');
                $filePayload['avatar_path'] = $request->file('avatar')->store('profiles/admins/avatars', 'public');
            }
        }

        return $filePayload;
    }

    private function deleteOldFile(?string $path, string $disk): void
    {
        if ($path && Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
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
                        'applied_at' => $application->applied_at?->toIso8601String(),
                        'status' => $application->status,
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
