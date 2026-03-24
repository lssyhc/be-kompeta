<?php

namespace App\Http\Resources\Partnership;

use App\Models\CompanyProfile;
use App\Models\PartnershipProposal;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PartnershipProposal */
class PartnershipProposalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $mitraUser = $this->mitraUser;

        return [
            'id' => $this->id,
            'proposer_user_id' => $this->proposer_user_id,
            'target_user_id' => $this->target_user_id,
            'school_user_id' => $this->school_user_id,
            'mitra_user_id' => $this->mitra_user_id,
            'nama_mitra' => $this->resolveMitraName($mitraUser),
            'mitra_tipe' => $mitraUser instanceof User ? $mitraUser->mitra_type : null,
            'sektor_atau_tipe' => $this->resolveMitraSectorOrType($mitraUser),
            'notes' => $this->notes,
            'status_submit' => $this->status,
            'tanggal_submit' => $this->submitted_at?->toIso8601String(),
            'proposal_pdf_url' => url("/api/partnership-proposals/{$this->id}/proposal-pdf"),
            'signature_url' => url("/api/partnership-proposals/{$this->id}/signature"),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function resolveMitraName(mixed $mitraUser): ?string
    {
        if (! $mitraUser instanceof User) {
            return null;
        }

        if ($mitraUser->mitra_type === User::MITRA_PERUSAHAAN) {
            $profile = $mitraUser->companyProfile;

            if ($profile instanceof CompanyProfile) {
                return $profile->company_name;
            }
        }

        if ($mitraUser->mitra_type === User::MITRA_UMKM) {
            $profile = $mitraUser->umkmProfile;

            if ($profile instanceof UmkmProfile) {
                return $profile->business_name;
            }
        }

        return $mitraUser->name;
    }

    private function resolveMitraSectorOrType(mixed $mitraUser): ?string
    {
        if (! $mitraUser instanceof User) {
            return null;
        }

        if ($mitraUser->mitra_type === User::MITRA_PERUSAHAAN) {
            $profile = $mitraUser->companyProfile;

            return $profile instanceof CompanyProfile
                ? $profile->industry_sector
                : null;
        }

        if ($mitraUser->mitra_type === User::MITRA_UMKM) {
            $profile = $mitraUser->umkmProfile;

            return $profile instanceof UmkmProfile
                ? $profile->business_type
                : null;
        }

        return null;
    }
}
