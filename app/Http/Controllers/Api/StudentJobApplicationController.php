<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentJobApplicationRequest;
use App\Http\Resources\Student\StudentJobApplicationResource;
use App\Models\JobVacancy;
use App\Models\StudentApplication;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentJobApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $studentProfile = $this->resolveStudentProfile($request);

        if (! $studentProfile instanceof StudentProfile) {
            return $this->errorResponse('Hanya user siswa yang dapat melihat lamaran.', 403);
        }

        $perPage = max(1, min(50, (int) $request->query('per_page', 12)));

        $paginator = StudentApplication::query()
            ->where('student_profile_id', $studentProfile->id)
            ->with(['jobVacancy'])
            ->orderByDesc('applied_at')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->appends($request->query());

        $paginator->setCollection(
            collect(StudentJobApplicationResource::collection($paginator->getCollection())->resolve())
        );

        return $this->paginatedResponse($paginator, 'Riwayat lamaran berhasil diambil.');
    }

    public function store(StoreStudentJobApplicationRequest $request): JsonResponse
    {
        $studentProfile = $this->resolveStudentProfile($request);

        if (! $studentProfile instanceof StudentProfile) {
            return $this->errorResponse('Hanya user siswa yang dapat melamar lowongan.', 403);
        }

        $validated = $request->validated();

        $jobVacancy = JobVacancy::query()
            ->published()
            ->where('id', $validated['job_vacancy_id'])
            ->with(['mitraUser.companyProfile', 'mitraUser.umkmProfile'])
            ->first();

        if (! $jobVacancy instanceof JobVacancy) {
            return $this->errorResponse('Lowongan tidak ditemukan atau belum dipublikasikan.', 404);
        }

        $alreadyApplied = StudentApplication::query()
            ->where('student_profile_id', $studentProfile->id)
            ->where('job_vacancy_id', $jobVacancy->id)
            ->where('status', StudentApplication::STATUS_SUBMITTED)
            ->exists();

        if ($alreadyApplied) {
            return $this->errorResponse('Lamaran untuk lowongan ini sudah ada.', 422);
        }

        $now = now();

        $application = StudentApplication::query()->create([
            'student_profile_id' => $studentProfile->id,
            'job_vacancy_id' => $jobVacancy->id,
            'mitra_user_id' => $jobVacancy->mitra_user_id,
            'company_name' => $jobVacancy->resolveMitraName() ?? '',
            'role_type' => $jobVacancy->position_name,
            'cv_path' => $request->file('cv')->store('student-applications/cv', 'local'),
            'cover_letter' => $validated['cover_letter'] ?? null,
            'status' => StudentApplication::STATUS_SUBMITTED,
            'applied_at' => $now,
        ]);

        $application->loadMissing(['jobVacancy']);

        return $this->successResponse(
            (new StudentJobApplicationResource($application))->resolve(),
            'Lamaran pekerjaan berhasil dikirim.',
            201
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $studentProfile = $this->resolveStudentProfile($request);

        if (! $studentProfile instanceof StudentProfile) {
            return $this->errorResponse('Hanya user siswa yang dapat melihat detail lamaran.', 403);
        }

        $application = StudentApplication::query()
            ->where('id', $id)
            ->where('student_profile_id', $studentProfile->id)
            ->with(['jobVacancy'])
            ->first();

        if (! $application instanceof StudentApplication) {
            return $this->errorResponse('Lamaran tidak ditemukan.', 404);
        }

        return $this->successResponse(
            (new StudentJobApplicationResource($application))->resolve(),
            'Detail lamaran berhasil diambil.'
        );
    }

    public function downloadCv(Request $request, int $id): mixed
    {
        $studentProfile = $this->resolveStudentProfile($request);

        if (! $studentProfile instanceof StudentProfile) {
            return $this->errorResponse('Hanya user siswa yang dapat mengunduh CV.', 403);
        }

        $application = StudentApplication::query()
            ->where('id', $id)
            ->where('student_profile_id', $studentProfile->id)
            ->first();

        if (! $application instanceof StudentApplication) {
            return $this->errorResponse('Lamaran tidak ditemukan.', 404);
        }

        $path = $application->cv_path;

        if (! is_string($path) || $path === '' || ! Storage::disk('local')->exists($path)) {
            return $this->errorResponse('Dokumen CV tidak ditemukan.', 404);
        }

        return Storage::disk('local')->download($path);
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
