<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Student\StudentBookmarkResource;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentBookmarkController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveStudentUser($request);

        if (! $user instanceof User) {
            return $this->errorResponse('Hanya user siswa yang dapat melihat bookmark.', 403);
        }

        $perPage = max(1, min(50, (int) $request->query('per_page', 12)));

        $paginator = $user->bookmarkedJobVacancies()
            ->where('is_published', true)
            ->with(['skills', 'benefits', 'mitraUser.companyProfile', 'mitraUser.umkmProfile'])
            ->orderByPivot('created_at', 'desc')
            ->paginate($perPage)
            ->appends($request->query());

        $paginator->setCollection(
            collect(StudentBookmarkResource::collection($paginator->getCollection())->resolve())
        );

        return $this->paginatedResponse($paginator, 'Daftar bookmark berhasil diambil.');
    }

    public function store(Request $request, int $jobVacancyId): JsonResponse
    {
        $user = $this->resolveStudentUser($request);

        if (! $user instanceof User) {
            return $this->errorResponse('Hanya user siswa yang dapat menambahkan bookmark.', 403);
        }

        $jobVacancy = JobVacancy::query()->published()->find($jobVacancyId);

        if (! $jobVacancy instanceof JobVacancy) {
            return $this->errorResponse('Lowongan tidak ditemukan atau belum dipublikasikan.', 404);
        }

        $alreadyBookmarked = $user->bookmarkedJobVacancies()->where('job_vacancy_id', $jobVacancy->id)->exists();

        if ($alreadyBookmarked) {
            return $this->errorResponse('Lowongan sudah ada di bookmark.', 422);
        }

        $user->bookmarkedJobVacancies()->attach($jobVacancy->id);

        return $this->successResponse(null, 'Lowongan berhasil ditambahkan ke bookmark.', 201);
    }

    public function destroy(Request $request, int $jobVacancyId): JsonResponse
    {
        $user = $this->resolveStudentUser($request);

        if (! $user instanceof User) {
            return $this->errorResponse('Hanya user siswa yang dapat menghapus bookmark.', 403);
        }

        $detached = $user->bookmarkedJobVacancies()->detach($jobVacancyId);

        if ($detached === 0) {
            return $this->errorResponse('Bookmark tidak ditemukan.', 404);
        }

        return $this->successResponse(null, 'Bookmark berhasil dihapus.');
    }

    private function resolveStudentUser(Request $request): ?User
    {
        $user = $request->user();

        if (! $user instanceof User || $user->role !== User::ROLE_SISWA) {
            return null;
        }

        return $user;
    }
}
