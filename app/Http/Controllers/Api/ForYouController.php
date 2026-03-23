<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForYou\ForYouIndexRequest;
use App\Http\Resources\Explore\ExploreJobCardResource;
use App\Http\Resources\ForYou\ForYouJobDetailResource;
use App\Models\JobVacancy;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class ForYouController extends Controller
{
    public function index(ForYouIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $sortBy = (string) ($validated['sort_by'] ?? 'paling_relevan');
        $perPage = (int) ($validated['per_page'] ?? 12);

        $preferences = $this->resolvePreferences($validated);

        $query = JobVacancy::query()
            ->published()
            ->with([
                'mitraUser.companyProfile',
                'mitraUser.umkmProfile',
                'skills',
                'benefits',
            ]);

        $this->applyPreferenceScoring($query, $preferences);
        $this->applySort($query, $sortBy);

        $paginator = $query
            ->paginate($perPage)
            ->appends($request->query());

        $paginator->setCollection(
            collect(ExploreJobCardResource::collection($paginator->getCollection())->resolve())
        );

        return $this->paginatedResponse($paginator, 'Daftar rekomendasi lowongan berhasil diambil.');
    }

    public function show(string $slug): JsonResponse
    {
        $job = JobVacancy::query()
            ->published()
            ->where('slug', $slug)
            ->with([
                'mitraUser.companyProfile',
                'mitraUser.umkmProfile',
                'skills',
                'benefits',
            ])
            ->first();

        if (! $job) {
            return $this->errorResponse('Lowongan tidak ditemukan.', 404);
        }

        return $this->successResponse([
            'job' => (new ForYouJobDetailResource($job))->resolve(),
        ], 'Detail rekomendasi lowongan berhasil diambil.');
    }

    public function sortOptions(): JsonResponse
    {
        return $this->successResponse([
            'sort_by' => [
                ['value' => 'paling_relevan', 'label' => 'Paling Relevan'],
                ['value' => 'baru_ditambahkan', 'label' => 'Baru Ditambahkan'],
                ['value' => 'remote_wfh', 'label' => 'Remote/WFH'],
                ['value' => 'hybrid', 'label' => 'Hybrid'],
                ['value' => 'part_time', 'label' => 'Part-time'],
            ],
        ], 'Opsi urutan for you berhasil diambil.');
    }

    private function resolvePreferences(array $validated): array
    {
        $querySkills = collect($validated['preferred_skills'] ?? [])
            ->filter(fn ($skill) => is_string($skill) && trim($skill) !== '')
            ->map(fn (string $skill) => mb_strtolower(trim($skill)))
            ->unique()
            ->values();

        $studentSkills = collect();
        $sanctumUser = auth('sanctum')->user();

        if ($sanctumUser instanceof User && $sanctumUser->role === User::ROLE_SISWA) {
            $sanctumUser->loadMissing('studentProfile.skills');
            $studentProfile = $sanctumUser->studentProfile;

            if ($studentProfile instanceof StudentProfile) {
                $studentSkills = $studentProfile->skills
                    ->pluck('title')
                    ->filter(fn ($skill) => is_string($skill) && trim($skill) !== '')
                    ->map(fn (string $skill) => mb_strtolower(trim($skill)))
                    ->unique()
                    ->values();
            }
        }

        $skills = $querySkills
            ->merge($studentSkills)
            ->unique()
            ->values()
            ->all();

        return [
            'skills' => $skills,
            'work_policies' => array_values(array_unique($validated['preferred_work_policies'] ?? [])),
            'job_types' => array_values(array_unique($validated['preferred_job_types'] ?? [])),
            'province' => $validated['preferred_province'] ?? null,
        ];
    }

    private function applyPreferenceScoring(Builder $query, array $preferences): void
    {
        $query->select('job_vacancies.*');

        if (count($preferences['skills']) > 0) {
            $placeholders = implode(',', array_fill(0, count($preferences['skills']), '?'));

            $query->withCount([
                'skills as matched_skill_count' => fn ($skillQuery) => $skillQuery
                    ->whereRaw("LOWER(name) IN ({$placeholders})", $preferences['skills']),
            ]);
        } else {
            $query->selectRaw('0 as matched_skill_count');
        }

        $this->appendMatchScoreColumn($query, 'work_policy', 'work_policy_match_score', $preferences['work_policies']);
        $this->appendMatchScoreColumn($query, 'job_type', 'job_type_match_score', $preferences['job_types']);

        if (is_string($preferences['province']) && $preferences['province'] !== '') {
            $query->selectRaw('CASE WHEN province = ? THEN 1 ELSE 0 END as province_match_score', [$preferences['province']]);
        } else {
            $query->selectRaw('0 as province_match_score');
        }
    }

    private function appendMatchScoreColumn(Builder $query, string $column, string $alias, array $preferredValues): void
    {
        if (count($preferredValues) < 1) {
            $query->selectRaw("0 as {$alias}");

            return;
        }

        $placeholders = implode(',', array_fill(0, count($preferredValues), '?'));

        $query->selectRaw("CASE WHEN {$column} IN ({$placeholders}) THEN 1 ELSE 0 END as {$alias}", $preferredValues);
    }

    private function applySort(Builder $query, string $sortBy): void
    {
        if ($sortBy === 'baru_ditambahkan') {
            $query
                ->orderByDesc('created_at')
                ->orderByDesc('updated_at');

            return;
        }

        if ($sortBy === 'remote_wfh') {
            $query
                ->orderByRaw('CASE WHEN work_policy = ? THEN 2 WHEN work_policy = ? THEN 1 ELSE 0 END DESC', [JobVacancy::WORK_POLICY_REMOTE, JobVacancy::WORK_POLICY_HYBRID])
                ->orderByDesc('matched_skill_count')
                ->orderByDesc('updated_at');

            return;
        }

        if ($sortBy === 'hybrid') {
            $query
                ->orderByRaw('CASE WHEN work_policy = ? THEN 2 WHEN work_policy = ? THEN 1 ELSE 0 END DESC', [JobVacancy::WORK_POLICY_HYBRID, JobVacancy::WORK_POLICY_REMOTE])
                ->orderByDesc('matched_skill_count')
                ->orderByDesc('updated_at');

            return;
        }

        if ($sortBy === 'part_time') {
            $query
                ->orderByRaw('CASE WHEN job_type = ? THEN 2 WHEN job_type = ? THEN 1 ELSE 0 END DESC', [JobVacancy::JOB_TYPE_PART_TIME, JobVacancy::JOB_TYPE_FREELANCE])
                ->orderByDesc('matched_skill_count')
                ->orderByDesc('updated_at');

            return;
        }

        $query
            ->orderByDesc('matched_skill_count')
            ->orderByDesc('work_policy_match_score')
            ->orderByDesc('job_type_match_score')
            ->orderByDesc('province_match_score')
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at');
    }
}
