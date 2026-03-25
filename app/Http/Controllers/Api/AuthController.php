<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\AdminProfile;
use App\Models\CompanyProfile;
use App\Models\SchoolProfile;
use App\Models\StudentProfile;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($request, $validated) {
            $role = $validated['role'];
            $mitraType = $validated['mitra_type'] ?? null;

            $user = User::query()->create([
                'name' => $this->resolveDisplayName($validated),
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => $role,
                'mitra_type' => $mitraType,
                'account_status' => $this->resolveInitialStatus($role),
            ]);

            if ($role === User::ROLE_SEKOLAH) {
                SchoolProfile::query()->create([
                    'user_id' => $user->id,
                    'school_name' => $validated['school_name'],
                    'npsn' => $validated['npsn'],
                    'accreditation' => $validated['accreditation'],
                    'address' => $validated['address'],
                    'socials' => $validated['socials'] ?? null,
                    'expertise_fields' => $validated['expertise_fields'],
                    'logo_path' => $request->file('logo')->store('profiles/schools/logos', 'public'),
                    'image_1_path' => $request->file('image_1')?->store('profiles/schools/images', 'public'),
                    'image_2_path' => $request->file('image_2')?->store('profiles/schools/images', 'public'),
                    'image_3_path' => $request->file('image_3')?->store('profiles/schools/images', 'public'),
                    'image_4_path' => $request->file('image_4')?->store('profiles/schools/images', 'public'),
                    'image_5_path' => $request->file('image_5')?->store('profiles/schools/images', 'public'),
                    'short_description' => $validated['short_description'],
                    'operational_license_path' => $request->file('operational_license')->store('profiles/schools/legalities', 'local'),
                ]);
            }

            if ($role === User::ROLE_MITRA && $mitraType === User::MITRA_PERUSAHAAN) {
                CompanyProfile::query()->create([
                    'user_id' => $user->id,
                    'company_name' => $validated['company_name'],
                    'nib' => $validated['nib'],
                    'industry_sector' => $validated['industry_sector'],
                    'employee_total_range' => $validated['employee_total_range'],
                    'office_address' => $validated['office_address'],
                    'socials' => $validated['socials'] ?? null,
                    'short_description' => $validated['short_description'],
                    'company_logo_path' => $request->file('company_logo')->store('profiles/companies/logos', 'public'),
                    'image_1_path' => $request->file('image_1')?->store('profiles/companies/images', 'public'),
                    'image_2_path' => $request->file('image_2')?->store('profiles/companies/images', 'public'),
                    'image_3_path' => $request->file('image_3')?->store('profiles/companies/images', 'public'),
                    'image_4_path' => $request->file('image_4')?->store('profiles/companies/images', 'public'),
                    'image_5_path' => $request->file('image_5')?->store('profiles/companies/images', 'public'),
                    'kemenkumham_decree_path' => $request->file('kemenkumham_decree')->store('profiles/companies/legalities', 'local'),
                ]);
            }

            if ($role === User::ROLE_MITRA && $mitraType === User::MITRA_UMKM) {
                UmkmProfile::query()->create([
                    'user_id' => $user->id,
                    'business_name' => $validated['business_name'],
                    'owner_nik' => $validated['owner_nik'],
                    'owner_personal_nib' => $validated['owner_personal_nib'] ?? null,
                    'business_type' => $validated['business_type'],
                    'business_address' => $validated['business_address'],
                    'socials' => $validated['socials'] ?? null,
                    'umkm_logo_path' => $request->file('umkm_logo')->store('profiles/umkm/logos', 'public'),
                    'owner_ktp_photo_path' => $request->file('owner_ktp_photo')->store('profiles/umkm/ktp', 'local'),
                    'short_description' => $validated['short_description'],
                    'image_1_path' => $request->file('image_1')->store('profiles/umkm/images', 'public'),
                    'image_2_path' => $request->file('image_2')->store('profiles/umkm/images', 'public'),
                    'image_3_path' => $request->file('image_3')->store('profiles/umkm/images', 'public'),
                    'image_4_path' => $request->file('image_4')->store('profiles/umkm/images', 'public'),
                    'image_5_path' => $request->file('image_5')->store('profiles/umkm/images', 'public'),
                ]);
            }

            if ($role === User::ROLE_ADMIN) {
                AdminProfile::query()->create([
                    'user_id' => $user->id,
                    'full_name' => $validated['name'] ?? $validated['email'],
                    'avatar_path' => User::DEFAULT_PROFILE_PHOTO_PATH,
                ]);
            }

            return $user;
        });

        return $this->successResponse([
            'user' => $this->compactUser($user),
            'role_profile' => $this->resolveRoleProfile($user),
        ], 'Registrasi berhasil. Mohon menunggu persetujuan dari Tim Kompeta untuk pengaktifan akun, informasi selanjutnya akan dikirimkan melalui email yang Anda daftarkan.', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $role = $validated['role'];

        $user = $role === User::ROLE_SISWA
            ? $this->resolveStudentLoginUser($validated)
            : $this->resolveEmailPasswordLoginUser($validated, $role);

        if (! $user) {
            return $this->errorResponse('Kredensial login tidak valid.', 422);
        }

        if ($user->account_status === User::STATUS_PENDING) {
            return $this->errorResponse('Akun belum disetujui. Mohon menunggu persetujuan dari Tim Kompeta untuk pengaktifan akun. Pemberitahuan pengaktifan akan dikirimkan melalui email Anda nanti.', 403);
        }

        if ($user->account_status === User::STATUS_REJECTED) {
            return $this->errorResponse('Akun tidak disetujui. Silakan periksa email yang Anda daftarkan untuk membaca informasi penolakan lebih lanjut dari Tim Kompeta.', 403);
        }

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        $token = $user->createToken(
            'api-client',
            [$user->role]
        )->plainTextToken;

        $onboarding = $this->resolveOnboardingState($user);

        return $this->successResponse([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->compactUser($user),
            'role_profile' => $this->resolveRoleProfile($user),
            'requires_skill_setup' => $onboarding['requires_skill_setup'],
            'next_step' => $onboarding['next_step'],
        ], 'Login berhasil.');
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $user->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logout berhasil.');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $onboarding = $this->resolveOnboardingState($user);

        return $this->successResponse([
            'user' => $this->compactUser($user),
            'role_profile' => $this->resolveRoleProfile($user),
            'requires_skill_setup' => $onboarding['requires_skill_setup'],
            'next_step' => $onboarding['next_step'],
        ], 'Data user berhasil diambil.');
    }

    private function resolveDisplayName(array $validated): string
    {
        return match ($validated['role']) {
            User::ROLE_SEKOLAH => $validated['school_name'],
            User::ROLE_MITRA => $validated['mitra_type'] === User::MITRA_PERUSAHAAN
                ? $validated['company_name']
                : $validated['business_name'],
            User::ROLE_ADMIN => $validated['name'] ?? $validated['email'],
            default => $validated['email'],
        };
    }

    private function resolveInitialStatus(string $role): string
    {
        if (in_array($role, [User::ROLE_SEKOLAH, User::ROLE_MITRA], true)) {
            return User::STATUS_PENDING;
        }

        return User::STATUS_ACTIVE;
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
        if ($user->role === User::ROLE_SEKOLAH) {
            return SchoolProfile::query()->where('user_id', $user->id)->first();
        }

        if ($user->role === User::ROLE_MITRA && $user->mitra_type === User::MITRA_PERUSAHAAN) {
            return CompanyProfile::query()->where('user_id', $user->id)->first();
        }

        if ($user->role === User::ROLE_MITRA && $user->mitra_type === User::MITRA_UMKM) {
            return UmkmProfile::query()->where('user_id', $user->id)->first();
        }

        if ($user->role === User::ROLE_SISWA) {
            return StudentProfile::query()->where('user_id', $user->id)->first();
        }

        if ($user->role === User::ROLE_ADMIN) {
            return AdminProfile::query()->where('user_id', $user->id)->first();
        }

        return null;
    }

    private function resolveEmailPasswordLoginUser(array $validated, string $role): ?User
    {
        $user = User::query()
            ->where('email', $validated['email'])
            ->where('role', $role)
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return null;
        }

        return $user;
    }

    private function resolveStudentLoginUser(array $validated): ?User
    {
        $studentProfile = StudentProfile::query()
            ->where('nisn', $validated['nisn'])
            ->where('school_origin', $validated['school_origin'])
            ->where('unique_code', $validated['unique_code'])
            ->first();

        if (! $studentProfile) {
            return null;
        }

        $studentProfile->loadMissing('user');

        $studentUser = $studentProfile->user;

        if (! $studentUser instanceof User) {
            return null;
        }

        return $studentUser->role === User::ROLE_SISWA
            ? $studentUser
            : null;
    }

    private function resolveOnboardingState(User $user): array
    {
        if ($user->role !== User::ROLE_SISWA) {
            return [
                'requires_skill_setup' => false,
                'next_step' => null,
            ];
        }

        $studentProfile = $user->studentProfile;

        if (! $studentProfile instanceof StudentProfile) {
            return [
                'requires_skill_setup' => true,
                'next_step' => 'add_skill',
            ];
        }

        $hasSkill = $studentProfile->skills()->exists();
        $requiresSkillSetup = ! $hasSkill;

        return [
            'requires_skill_setup' => $requiresSkillSetup,
            'next_step' => $requiresSkillSetup ? 'add_skill' : null,
        ];
    }
}
