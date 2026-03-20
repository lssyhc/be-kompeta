<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Explore\ExploreIndexRequest;
use App\Http\Resources\Explore\ExploreJobCardResource;
use App\Http\Resources\Explore\ExploreJobDetailResource;
use App\Models\JobVacancy;
use Illuminate\Http\JsonResponse;

class ExploreController extends Controller
{
    public function index(ExploreIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $keyword = trim((string) ($validated['q'] ?? ''));
        $normalizedKeyword = mb_strtolower($keyword);
        $perPage = (int) ($validated['per_page'] ?? 12);

        $query = JobVacancy::query()
            ->published()
            ->with([
                'mitraUser.companyProfile',
                'mitraUser.umkmProfile',
                'skills',
                'benefits',
            ]);

        if ($keyword !== '') {
            $query->where(function ($q) use ($normalizedKeyword) {
                $pattern = "%{$normalizedKeyword}%";

                $q->whereRaw('LOWER(position_name) LIKE ?', [$pattern])
                    ->orWhereHas('skills', fn ($sq) => $sq->whereRaw('LOWER(name) LIKE ?', [$pattern]))
                    ->orWhereHas('mitraUser', function ($uq) use ($pattern) {
                        $uq->whereRaw('LOWER(name) LIKE ?', [$pattern])
                            ->orWhereHas('companyProfile', fn ($cq) => $cq->whereRaw('LOWER(company_name) LIKE ?', [$pattern]))
                            ->orWhereHas('umkmProfile', fn ($mq) => $mq->whereRaw('LOWER(business_name) LIKE ?', [$pattern]));
                    });
            });
        }

        $query
            ->when(! empty($validated['job_types']), fn ($q) => $q->whereIn('job_type', $validated['job_types']))
            ->when(! empty($validated['work_policies']), fn ($q) => $q->whereIn('work_policy', $validated['work_policies']))
            ->when(! empty($validated['provinces']), fn ($q) => $q->whereIn('province', $validated['provinces']))
            ->when(! empty($validated['experience_levels']), fn ($q) => $q->whereIn('experience_level', $validated['experience_levels']));

        $updatedWithin = $validated['updated_within'] ?? 'any';

        if ($updatedWithin === '24h') {
            $query->where('updated_at', '>=', now()->subDay());
        }

        if ($updatedWithin === '7d') {
            $query->where('updated_at', '>=', now()->subDays(7));
        }

        if ($updatedWithin === '30d') {
            $query->where('updated_at', '>=', now()->subDays(30));
        }

        $paginator = $query
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->appends($request->query());

        $paginator->setCollection(
            collect(ExploreJobCardResource::collection($paginator->getCollection())->resolve())
        );

        return $this->paginatedResponse($paginator, 'Daftar lowongan berhasil diambil.');
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

        $recommendations = JobVacancy::query()
            ->published()
            ->where('id', '!=', $job->id)
            ->where(function ($query) use ($job) {
                $query->where('province', $job->province)
                    ->orWhere('job_type', $job->job_type);
            })
            ->with([
                'mitraUser.companyProfile',
                'mitraUser.umkmProfile',
                'skills',
                'benefits',
            ])
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        if ($recommendations->count() < 6) {
            $excludedIds = $recommendations->pluck('id')->push($job->id)->values();

            $additionalRecommendations = JobVacancy::query()
                ->published()
                ->whereNotIn('id', $excludedIds)
                ->with([
                    'mitraUser.companyProfile',
                    'mitraUser.umkmProfile',
                    'skills',
                    'benefits',
                ])
                ->orderByDesc('updated_at')
                ->orderByDesc('created_at')
                ->limit(6 - $recommendations->count())
                ->get();

            $recommendations = $recommendations->concat($additionalRecommendations)->values();
        }

        return $this->successResponse([
            'job' => (new ExploreJobDetailResource($job))->resolve(),
            'recommendations' => ExploreJobCardResource::collection($recommendations)->resolve(),
        ], 'Detail lowongan berhasil diambil.');
    }

    public function filterOptions(): JsonResponse
    {
        $jobTypes = collect(JobVacancy::jobTypeLabels())
            ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->all();

        $workPolicies = collect(JobVacancy::workPolicyLabels())
            ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->all();

        $experienceLevels = collect(JobVacancy::experienceLabels())
            ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->all();

        return $this->successResponse([
            'job_types' => $jobTypes,
            'work_policies' => $workPolicies,
            'provinces' => config('indonesia.provinces', []),
            'experience_levels' => $experienceLevels,
            'updated_within' => [
                ['value' => '7d', 'label' => 'Seminggu terakhir'],
                ['value' => '24h', 'label' => '24 jam terakhir'],
                ['value' => '30d', 'label' => '1 bulan terakhir'],
                ['value' => 'any', 'label' => 'Kapan pun'],
            ],
        ], 'Opsi filter explore berhasil diambil.');
    }
}
