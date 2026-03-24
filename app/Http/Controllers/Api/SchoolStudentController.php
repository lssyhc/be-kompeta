<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\School\StoreStudentRequest;
use App\Http\Requests\School\UpdateStudentRequest;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SchoolStudentController extends Controller
{
    public function store(StoreStudentRequest $request): JsonResponse
    {
        $schoolUser = $request->user();

        if ($schoolUser->role !== User::ROLE_SEKOLAH) {
            return $this->errorResponse('Hanya user sekolah yang dapat menambahkan siswa.', 403);
        }

        $validated = $request->validated();

        $student = DB::transaction(function () use ($request, $validated, $schoolUser) {
            $studentUser = User::query()->create([
                'name' => $validated['full_name'],
                'email' => null,
                'password' => Str::random(32),
                'role' => User::ROLE_SISWA,
                'account_status' => 'active',
            ]);

            StudentProfile::query()->create([
                'user_id' => $studentUser->id,
                'school_user_id' => $schoolUser->id,
                'full_name' => $validated['full_name'],
                'nisn' => $validated['nisn'],
                'photo_profile_path' => $request->file('photo_profile')?->store('profiles/students/photos', 'public'),
                'major' => $validated['major'],
                'school_origin' => $validated['school_origin'],
                'graduation_status' => $validated['graduation_status'],
                'unique_code' => $this->generateUniqueCode(),
                'class_year' => $validated['class_year'] ?? null,
                'phone_number' => $validated['phone_number'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);

            return $studentUser;
        });

        $student->load('studentProfile');

        return $this->successResponse([
            'user' => $student,
        ], 'Data siswa berhasil ditambahkan.', 201);
    }

    public function index(Request $request): JsonResponse
    {
        $schoolUser = $request->user();

        if ($schoolUser->role !== User::ROLE_SEKOLAH) {
            return $this->errorResponse('Hanya user sekolah yang dapat mengakses data siswa.', 403);
        }

        $students = StudentProfile::query()
            ->where('school_user_id', $schoolUser->id)
            ->when($request->query('class_year'), fn ($q, $year) => $q->where('class_year', $year))
            ->when($request->query('graduation_status'), fn ($q, $status) => $q->where('graduation_status', $status))
            ->with('user:id,role,account_status,last_login_at')
            ->orderByDesc('created_at')
            ->paginate(20);

        return $this->paginatedResponse($students, 'Daftar siswa berhasil diambil.');
    }

    public function search(Request $request): JsonResponse
    {
        $schoolUser = $request->user();

        if ($schoolUser->role !== User::ROLE_SEKOLAH) {
            return $this->errorResponse('Hanya user sekolah yang dapat mencari data siswa.', 403);
        }

        $request->validate([
            'q' => ['required', 'string', 'min:1', 'max:100'],
        ]);

        $keyword = $request->query('q');

        $students = StudentProfile::query()
            ->where('school_user_id', $schoolUser->id)
            ->where(function ($q) use ($keyword) {
                $q->where('full_name', 'like', "%{$keyword}%")
                    ->orWhere('nisn', 'like', "%{$keyword}%");
            })
            ->with('user:id,role,account_status,last_login_at')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return $this->successResponse([
            'results' => $students,
        ], 'Pencarian siswa berhasil.', 200, [
            'query' => $keyword,
            'total' => $students->count(),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $schoolUser = $request->user();

        if ($schoolUser->role !== User::ROLE_SEKOLAH) {
            return $this->errorResponse('Hanya user sekolah yang dapat mengakses data siswa.', 403);
        }

        $studentProfile = StudentProfile::query()
            ->where('id', $id)
            ->where('school_user_id', $schoolUser->id)
            ->with([
                'user:id,role,account_status,last_login_at',
                'skills',
                'experiences',
                'achievements',
                'applications',
            ])
            ->first();

        if (! $studentProfile) {
            return $this->errorResponse('Data siswa tidak ditemukan.', 404);
        }

        return $this->successResponse([
            'student' => $studentProfile,
        ], 'Detail siswa berhasil diambil.');
    }

    public function update(UpdateStudentRequest $request, int $id): JsonResponse
    {
        $schoolUser = $request->user();

        if ($schoolUser->role !== User::ROLE_SEKOLAH) {
            return $this->errorResponse('Hanya user sekolah yang dapat mengubah data siswa.', 403);
        }

        $studentProfile = StudentProfile::query()
            ->where('id', $id)
            ->where('school_user_id', $schoolUser->id)
            ->first();

        if (! $studentProfile) {
            return $this->errorResponse('Data siswa tidak ditemukan.', 404);
        }

        $validated = $request->validated();

        if ($request->hasFile('photo_profile')) {
            $validated['photo_profile_path'] = $request->file('photo_profile')->store('profiles/students/photos', 'public');
        }

        unset($validated['photo_profile']);

        DB::transaction(function () use ($studentProfile, $validated) {
            $studentProfile->update($validated);

            if (isset($validated['full_name'])) {
                $studentProfile->user->update(['name' => $validated['full_name']]);
            }
        });

        return $this->successResponse([
            'student' => $studentProfile->fresh(),
        ], 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $schoolUser = $request->user();

        if ($schoolUser->role !== User::ROLE_SEKOLAH) {
            return $this->errorResponse('Hanya user sekolah yang dapat menghapus data siswa.', 403);
        }

        $studentProfile = StudentProfile::query()
            ->where('id', $id)
            ->where('school_user_id', $schoolUser->id)
            ->with('user')
            ->first();

        if (! $studentProfile) {
            return $this->errorResponse('Data siswa tidak ditemukan.', 404);
        }

        DB::transaction(function () use ($studentProfile) {
            if ($studentProfile->user) {
                $studentProfile->user->delete();

                return;
            }

            $studentProfile->delete();
        });

        return $this->successResponse(null, 'Data siswa berhasil dihapus.');
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (StudentProfile::query()->where('unique_code', $code)->exists());

        return $code;
    }
}
