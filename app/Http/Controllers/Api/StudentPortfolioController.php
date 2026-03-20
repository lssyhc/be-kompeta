<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentPortfolioItemRequest;
use App\Models\StudentAchievement;
use App\Models\StudentApplication;
use App\Models\StudentExperience;
use App\Models\StudentProfile;
use App\Models\StudentSkill;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentPortfolioController extends Controller
{
    public function storePortfolioItem(StoreStudentPortfolioItemRequest $request): JsonResponse
    {
        $studentProfile = $this->resolveStudentProfile($request);

        if (! $studentProfile) {
            return $this->errorResponse('Hanya user siswa yang dapat mengelola portfolio.', 403);
        }

        $validated = $request->validated();

        $item = match ($validated['type']) {
            'skill' => StudentSkill::query()->create([
                'student_profile_id' => $studentProfile->id,
                'title' => $validated['title'] ?? '',
            ]),
            'experience' => StudentExperience::query()->create([
                'student_profile_id' => $studentProfile->id,
                'title' => $validated['title'] ?? '',
                'description' => $validated['description'] ?? '',
                'position' => $validated['position'] ?? '',
                'company_name' => $validated['company_name'] ?? '',
                'start_date' => $validated['start_date'] ?? now()->toDateString(),
                'end_date' => $validated['end_date'] ?? null,
            ]),
            'achievement' => StudentAchievement::query()->create([
                'student_profile_id' => $studentProfile->id,
                'title' => $validated['title'] ?? '',
                'description' => $validated['description'] ?? '',
                'achievement_date' => $validated['achievement_date'] ?? now()->toDateString(),
                'institution_name' => $validated['institution_name'] ?? '',
            ]),
            'application' => StudentApplication::query()->create([
                'student_profile_id' => $studentProfile->id,
                'company_name' => $validated['company_name'] ?? '',
                'role_type' => $validated['role_type'] ?? '',
                'submitted_at' => $validated['submitted_at'] ?? now()->toDateString(),
                'submit_status' => $validated['submit_status'] ?? 'submitted',
            ]),
            default => null,
        };

        return $this->successResponse([
            'item' => $item,
        ], 'Data portfolio berhasil ditambahkan.', 201);
    }

    public function applicationReminder(): JsonResponse
    {
        return $this->successResponse([
            'reminder' => 'Selalu cek informasi kontak email/nomor handphone yang anda cantumkan di cv',
        ], 'Reminder berhasil diambil.');
    }

    private function resolveStudentProfile(Request $request): ?StudentProfile
    {
        $user = $request->user();

        if (! $user instanceof User || $user->role !== User::ROLE_SISWA) {
            return null;
        }

        $studentProfile = $user->studentProfile;

        return $studentProfile instanceof StudentProfile ? $studentProfile : null;
    }
}
