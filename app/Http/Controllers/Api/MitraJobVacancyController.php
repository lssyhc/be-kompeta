<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mitra\StoreMitraJobVacancyRequest;
use App\Http\Requests\Mitra\UpdateMitraJobVacancyRequest;
use App\Http\Resources\Mitra\MitraJobVacancyResource;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MitraJobVacancyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveMitraUser($request);

        if (! $user instanceof User) {
            return $this->errorResponse('Hanya mitra yang dapat mengakses lowongan.', 403);
        }

        $perPage = max(1, min(50, (int) $request->query('per_page', 12)));

        $query = $user->jobVacancies()
            ->with(['skills', 'benefits'])
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at');

        $isPublished = $request->query('is_published');
        if ($isPublished !== null && in_array($isPublished, ['0', '1', 'true', 'false'], true)) {
            $query->where('is_published', filter_var($isPublished, FILTER_VALIDATE_BOOLEAN));
        }

        $paginator = $query
            ->paginate($perPage)
            ->appends($request->query());

        $paginator->setCollection(
            collect(MitraJobVacancyResource::collection($paginator->getCollection())->resolve())
        );

        return $this->paginatedResponse($paginator, 'Daftar lowongan berhasil diambil.');
    }

    public function store(StoreMitraJobVacancyRequest $request): JsonResponse
    {
        $user = $this->resolveMitraUser($request);

        if (! $user instanceof User) {
            return $this->errorResponse('Hanya mitra yang dapat membuat lowongan.', 403);
        }

        $validated = $request->validated();

        $slug = $this->generateUniqueSlug($validated['position_name']);

        $vacancy = JobVacancy::query()->create([
            'mitra_user_id' => $user->id,
            'slug' => $slug,
            'position_name' => $validated['position_name'],
            'category' => $validated['category'],
            'job_type' => $validated['job_type'],
            'work_policy' => $validated['work_policy'],
            'experience_level' => $validated['experience_level'],
            'province' => $validated['province'],
            'salary_min' => $validated['salary_min'] ?? null,
            'salary_max' => $validated['salary_max'] ?? null,
            'is_salary_hidden' => $validated['is_salary_hidden'] ?? false,
            'requirements' => $validated['requirements'] ?? null,
            'job_description' => $validated['job_description'],
            'is_published' => $validated['is_published'] ?? true,
        ]);

        $this->syncSkills($vacancy, $validated['skills'] ?? []);
        $this->syncBenefits($vacancy, $validated['benefits'] ?? []);

        $vacancy->loadMissing(['skills', 'benefits']);

        return $this->successResponse(
            (new MitraJobVacancyResource($vacancy))->resolve(),
            'Lowongan berhasil dibuat.',
            201
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $this->resolveMitraUser($request);

        if (! $user instanceof User) {
            return $this->errorResponse('Hanya mitra yang dapat mengakses lowongan.', 403);
        }

        $vacancy = $user->jobVacancies()
            ->with(['skills', 'benefits'])
            ->where('id', $id)
            ->first();

        if (! $vacancy instanceof JobVacancy) {
            return $this->errorResponse('Lowongan tidak ditemukan.', 404);
        }

        return $this->successResponse(
            (new MitraJobVacancyResource($vacancy))->resolve(),
            'Detail lowongan berhasil diambil.'
        );
    }

    public function update(UpdateMitraJobVacancyRequest $request, int $id): JsonResponse
    {
        $user = $this->resolveMitraUser($request);

        if (! $user instanceof User) {
            return $this->errorResponse('Hanya mitra yang dapat mengubah lowongan.', 403);
        }

        $vacancy = $user->jobVacancies()
            ->where('id', $id)
            ->first();

        if (! $vacancy instanceof JobVacancy) {
            return $this->errorResponse('Lowongan tidak ditemukan.', 404);
        }

        $validated = $request->validated();

        $fillableFields = [
            'position_name',
            'category',
            'job_type',
            'work_policy',
            'experience_level',
            'province',
            'salary_min',
            'salary_max',
            'is_salary_hidden',
            'requirements',
            'job_description',
            'is_published',
        ];

        $vacancy->update(array_intersect_key($validated, array_flip($fillableFields)));

        if (array_key_exists('skills', $validated)) {
            $this->syncSkills($vacancy, $validated['skills'] ?? []);
        }

        if (array_key_exists('benefits', $validated)) {
            $this->syncBenefits($vacancy, $validated['benefits'] ?? []);
        }

        $vacancy->loadMissing(['skills', 'benefits']);

        return $this->successResponse(
            (new MitraJobVacancyResource($vacancy))->resolve(),
            'Lowongan berhasil diperbarui.'
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $this->resolveMitraUser($request);

        if (! $user instanceof User) {
            return $this->errorResponse('Hanya mitra yang dapat menghapus lowongan.', 403);
        }

        $vacancy = $user->jobVacancies()
            ->where('id', $id)
            ->first();

        if (! $vacancy instanceof JobVacancy) {
            return $this->errorResponse('Lowongan tidak ditemukan.', 404);
        }

        $vacancy->delete();

        return $this->successResponse(null, 'Lowongan berhasil dihapus.');
    }

    private function resolveMitraUser(Request $request): ?User
    {
        $user = $request->user();

        if (! $user instanceof User || $user->role !== User::ROLE_MITRA) {
            return null;
        }

        return $user;
    }

    private function generateUniqueSlug(string $positionName): string
    {
        $base = Str::slug($positionName);
        $slug = $base.'-'.Str::lower(Str::random(6));

        while (JobVacancy::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.Str::lower(Str::random(6));
        }

        return $slug;
    }

    private function syncSkills(JobVacancy $vacancy, array $skills): void
    {
        $vacancy->skills()->delete();

        foreach ($skills as $skill) {
            $trimmed = trim($skill);
            if ($trimmed !== '') {
                $vacancy->skills()->create(['name' => $trimmed]);
            }
        }
    }

    private function syncBenefits(JobVacancy $vacancy, array $benefits): void
    {
        $vacancy->benefits()->delete();

        foreach ($benefits as $benefit) {
            $trimmed = trim($benefit);
            if ($trimmed !== '') {
                $vacancy->benefits()->create(['name' => $trimmed]);
            }
        }
    }
}
