<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Partnership\StorePartnershipProposalRequest;
use App\Http\Resources\Partnership\PartnershipProposalResource;
use App\Models\PartnershipProposal;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PartnershipProposalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $scope = (string) $request->query('scope', 'sent');
        $perPage = max(1, min(50, (int) $request->query('per_page', 12)));

        $query = PartnershipProposal::query()
            ->with(['mitraUser.companyProfile', 'mitraUser.umkmProfile'])
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at');

        if ($scope === 'received') {
            $query->where('target_user_id', $user->id);
        } else {
            $query->where('proposer_user_id', $user->id);
        }

        $paginator = $query
            ->paginate($perPage)
            ->appends($request->query());

        $paginator->setCollection(
            collect(PartnershipProposalResource::collection($paginator->getCollection())->resolve())
        );

        return $this->paginatedResponse($paginator, 'Daftar pengajuan kemitraan berhasil diambil.');
    }

    public function store(StorePartnershipProposalRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        if (! in_array($user->role, [User::ROLE_SEKOLAH, User::ROLE_MITRA], true)) {
            return $this->errorResponse('Hanya sekolah atau mitra yang dapat mengajukan kemitraan.', 403);
        }

        $validated = $request->validated();
        $targetUser = User::query()->find($validated['target_user_id']);

        if (! $targetUser instanceof User) {
            return $this->errorResponse('Target pengajuan tidak ditemukan.', 422);
        }

        if ($targetUser->id === $user->id) {
            return $this->errorResponse('Target pengajuan tidak valid.', 422);
        }

        if ($user->role === User::ROLE_SEKOLAH && $targetUser->role !== User::ROLE_MITRA) {
            return $this->errorResponse('Sekolah hanya bisa mengajukan ke mitra.', 422);
        }

        if ($user->role === User::ROLE_MITRA && $targetUser->role !== User::ROLE_SEKOLAH) {
            return $this->errorResponse('Mitra hanya bisa mengajukan ke sekolah.', 422);
        }

        $schoolUserId = $user->role === User::ROLE_SEKOLAH ? $user->id : $targetUser->id;
        $mitraUserId = $user->role === User::ROLE_MITRA ? $user->id : $targetUser->id;

        $existingOpenProposal = PartnershipProposal::query()
            ->where('proposer_user_id', $user->id)
            ->where('target_user_id', $targetUser->id)
            ->where('status', PartnershipProposal::STATUS_SUBMITTED)
            ->exists();

        if ($existingOpenProposal) {
            return $this->errorResponse('Pengajuan aktif untuk target ini sudah ada.', 422);
        }

        $proposal = PartnershipProposal::query()->create([
            'proposer_user_id' => $user->id,
            'target_user_id' => $targetUser->id,
            'school_user_id' => $schoolUserId,
            'mitra_user_id' => $mitraUserId,
            'proposal_pdf_path' => $request->file('proposal_pdf')->store('partnership/proposals', 'local'),
            'signature_path' => $request->file('signature_file')->store('partnership/signatures', 'local'),
            'notes' => $validated['notes'] ?? null,
            'status' => PartnershipProposal::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $proposal->loadMissing(['mitraUser.companyProfile', 'mitraUser.umkmProfile']);

        return $this->successResponse(
            (new PartnershipProposalResource($proposal))->resolve(),
            'Pengajuan kemitraan berhasil dikirim.',
            201
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $proposal = PartnershipProposal::query()
            ->where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('proposer_user_id', $user->id)
                    ->orWhere('target_user_id', $user->id);
            })
            ->with(['mitraUser.companyProfile', 'mitraUser.umkmProfile'])
            ->first();

        if (! $proposal instanceof PartnershipProposal) {
            return $this->errorResponse('Pengajuan kemitraan tidak ditemukan.', 404);
        }

        return $this->successResponse(
            (new PartnershipProposalResource($proposal))->resolve(),
            'Detail pengajuan kemitraan berhasil diambil.'
        );
    }

    public function downloadProposalPdf(Request $request, int $id): mixed
    {
        $proposal = $this->findAuthorizedProposal($request, $id);

        if (! $proposal instanceof PartnershipProposal) {
            return $this->errorResponse('Pengajuan kemitraan tidak ditemukan.', 404);
        }

        $path = $proposal->proposal_pdf_path;

        if (empty($path) || ! Storage::disk('local')->exists($path)) {
            return $this->errorResponse('Dokumen proposal tidak ditemukan.', 404);
        }

        return Storage::disk('local')->download($path);
    }

    public function downloadSignature(Request $request, int $id): mixed
    {
        $proposal = $this->findAuthorizedProposal($request, $id);

        if (! $proposal instanceof PartnershipProposal) {
            return $this->errorResponse('Pengajuan kemitraan tidak ditemukan.', 404);
        }

        $path = $proposal->signature_path;

        if (empty($path) || ! Storage::disk('local')->exists($path)) {
            return $this->errorResponse('Dokumen tanda tangan tidak ditemukan.', 404);
        }

        return Storage::disk('local')->download($path);
    }

    private function findAuthorizedProposal(Request $request, int $id): ?PartnershipProposal
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return null;
        }

        return PartnershipProposal::query()
            ->where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('proposer_user_id', $user->id)
                    ->orWhere('target_user_id', $user->id);
            })
            ->first();
    }
}
