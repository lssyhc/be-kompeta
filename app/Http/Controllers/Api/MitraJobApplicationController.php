<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mitra\MitraJobApplicationResource;
use App\Models\StudentApplication;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MitraJobApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveMitraUser($request);

        if (! $user instanceof User) {
            return $this->errorResponse('Hanya mitra yang dapat mengakses lamaran masuk.', 403);
        }

        $perPage = max(1, min(50, (int) $request->query('per_page', 12)));

        $query = StudentApplication::query()
            ->where('mitra_user_id', $user->id)
            ->with(['studentProfile', 'jobVacancy'])
            ->orderByDesc('applied_at')
            ->orderByDesc('created_at');

        $jobVacancyId = $request->query('job_vacancy_id');
        if (is_numeric($jobVacancyId)) {
            $query->where('job_vacancy_id', (int) $jobVacancyId);
        }

        $paginator = $query
            ->paginate($perPage)
            ->appends($request->query());

        $paginator->setCollection(
            collect(MitraJobApplicationResource::collection($paginator->getCollection())->resolve())
        );

        return $this->paginatedResponse($paginator, 'Daftar lamaran masuk berhasil diambil.');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $this->resolveMitraUser($request);

        if (! $user instanceof User) {
            return $this->errorResponse('Hanya mitra yang dapat mengakses detail lamaran.', 403);
        }

        $application = StudentApplication::query()
            ->where('id', $id)
            ->where('mitra_user_id', $user->id)
            ->with(['studentProfile.skills', 'studentProfile.experiences', 'studentProfile.achievements', 'jobVacancy'])
            ->first();

        if (! $application instanceof StudentApplication) {
            return $this->errorResponse('Lamaran tidak ditemukan.', 404);
        }

        return $this->successResponse(
            (new MitraJobApplicationResource($application))->resolve(),
            'Detail lamaran berhasil diambil.'
        );
    }

    public function downloadCv(Request $request, int $id): mixed
    {
        $user = $this->resolveMitraUser($request);

        if (! $user instanceof User) {
            return $this->errorResponse('Hanya mitra yang dapat mengunduh CV pelamar.', 403);
        }

        $application = StudentApplication::query()
            ->where('id', $id)
            ->where('mitra_user_id', $user->id)
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

    private function resolveMitraUser(Request $request): ?User
    {
        $user = $request->user();

        if (! $user instanceof User || $user->role !== User::ROLE_MITRA) {
            return null;
        }

        return $user;
    }
}
