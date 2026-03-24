<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Explore\ExploreJobCardResource;
use App\Http\Resources\Mitra\MitraCardResource;
use App\Http\Resources\Mitra\MitraDetailResource;
use App\Models\CompanyProfile;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class MitraController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $keyword = trim((string) ($request->query('q', '')));
        $type = $request->query('type');
        $perPage = max(1, min(50, (int) ($request->query('per_page', 12))));

        $query = User::query()
            ->where('role', User::ROLE_MITRA)
            ->where('account_status', User::STATUS_ACTIVE)
            ->with(['companyProfile', 'umkmProfile'])
            ->withCount(['jobVacancies as vacancy_count' => fn ($q) => $q->where('is_published', true)]);

        if ($type !== null && in_array($type, [User::MITRA_PERUSAHAAN, User::MITRA_UMKM], true)) {
            $query->where('mitra_type', $type);
        }

        if ($keyword !== '') {
            $pattern = '%'.mb_strtolower($keyword).'%';
            $query->where(function ($q) use ($pattern) {
                $q->whereHas('companyProfile', fn ($cq) => $cq->whereRaw('LOWER(company_name) LIKE ?', [$pattern]))
                    ->orWhereHas('umkmProfile', fn ($mq) => $mq->whereRaw('LOWER(business_name) LIKE ?', [$pattern]));
            });
        }

        $paginator = $query
            ->orderByDesc('last_login_at')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->appends($request->query());

        $paginator->setCollection(
            collect(MitraCardResource::collection($paginator->getCollection())->resolve())
        );

        return $this->paginatedResponse($paginator, 'Daftar mitra berhasil diambil.');
    }

    public function show(int $id): JsonResponse
    {
        $user = User::query()
            ->where('role', User::ROLE_MITRA)
            ->where('account_status', User::STATUS_ACTIVE)
            ->where('id', $id)
            ->with(['companyProfile', 'umkmProfile'])
            ->withCount(['jobVacancies as vacancy_count' => fn ($q) => $q->where('is_published', true)])
            ->first();

        if (! $user instanceof User) {
            return $this->errorResponse('Mitra tidak ditemukan.', 404);
        }

        $vacancies = $user->jobVacancies()
            ->where('is_published', true)
            ->with(['mitraUser.companyProfile', 'mitraUser.umkmProfile', 'skills', 'benefits'])
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->get();

        $similar = $this->resolveSimilarMitra($user);

        $detail = (new MitraDetailResource($user))->resolve();
        $detail['vacancies'] = ExploreJobCardResource::collection($vacancies)->resolve();
        $detail['similar_mitra'] = MitraCardResource::collection($similar)->resolve();

        return $this->successResponse($detail, 'Detail mitra berhasil diambil.');
    }

    private function resolveSimilarMitra(User $currentUser): Collection
    {
        $currentSector = $this->resolveUserSector($currentUser);
        $needed = 5;

        $sameTypeCandidates = User::query()
            ->where('role', User::ROLE_MITRA)
            ->where('account_status', User::STATUS_ACTIVE)
            ->where('mitra_type', $currentUser->mitra_type)
            ->where('id', '!=', $currentUser->id)
            ->with(['companyProfile', 'umkmProfile', 'featuredVacancy.skills'])
            ->withCount(['jobVacancies as vacancy_count' => fn ($q) => $q->where('is_published', true)])
            ->limit(50)
            ->get();

        $scored = $sameTypeCandidates
            ->map(fn (User $c) => [
                'user' => $c,
                'score' => $this->scoreSimilarMitra($c, $currentSector),
            ])
            ->sortByDesc('score')
            ->take($needed)
            ->pluck('user');

        if ($scored->count() >= $needed) {
            return $scored;
        }

        $excludeIds = $scored->pluck('id')->push($currentUser->id);

        $fillers = User::query()
            ->where('role', User::ROLE_MITRA)
            ->where('account_status', User::STATUS_ACTIVE)
            ->whereNotIn('id', $excludeIds)
            ->with(['companyProfile', 'umkmProfile', 'featuredVacancy.skills'])
            ->withCount(['jobVacancies as vacancy_count' => fn ($q) => $q->where('is_published', true)])
            ->orderByDesc('last_login_at')
            ->limit($needed - $scored->count())
            ->get();

        return $scored->concat($fillers)->take($needed);
    }

    private function scoreSimilarMitra(User $candidate, ?string $currentSector): int
    {
        $score = 0;

        $candidateSector = $this->resolveUserSector($candidate);

        if ($currentSector && $candidateSector && $candidateSector === $currentSector) {
            $score += 3;
        }

        if (($candidate->vacancy_count ?? 0) > 0) {
            $score += 1;
        }

        $lastLoginAt = $candidate->last_login_at;

        if ($lastLoginAt && Carbon::parse($lastLoginAt)->gte(now()->subDays(30))) {
            $score += 1;
        }

        return $score;
    }

    private function resolveUserSector(User $user): ?string
    {
        if ($user->mitra_type === User::MITRA_PERUSAHAAN) {
            return $user->companyProfile instanceof CompanyProfile
                ? $user->companyProfile->industry_sector
                : null;
        }

        if ($user->mitra_type === User::MITRA_UMKM) {
            return $user->umkmProfile instanceof UmkmProfile
                ? $user->umkmProfile->business_type
                : null;
        }

        return null;
    }
}
