<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\School\SchoolCardResource;
use App\Http\Resources\School\SchoolDetailResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $keyword = trim((string) ($request->query('q', '')));
        $perPage = max(1, min(50, (int) ($request->query('per_page', 12))));

        $query = User::query()
            ->where('role', User::ROLE_SEKOLAH)
            ->with(['schoolProfile']);

        if ($keyword !== '') {
            $pattern = '%'.mb_strtolower($keyword).'%';
            $query->whereHas('schoolProfile', fn ($q) => $q->whereRaw('LOWER(school_name) LIKE ?', [$pattern]));
        }

        $paginator = $query
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->appends($request->query());

        $paginator->setCollection(
            collect(SchoolCardResource::collection($paginator->getCollection())->resolve())
        );

        return $this->paginatedResponse($paginator, 'Daftar sekolah berhasil diambil.');
    }

    public function show(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = User::query()
            ->where('role', User::ROLE_SEKOLAH)
            ->where('id', $id)
            ->with([
                'schoolProfile',
                'managedStudents.achievements',
            ])
            ->first();

        if (! $user instanceof User) {
            return $this->errorResponse('Sekolah tidak ditemukan.', 404);
        }

        return $this->successResponse(
            (new SchoolDetailResource($user))->resolve(),
            'Detail sekolah berhasil diambil.'
        );
    }
}
