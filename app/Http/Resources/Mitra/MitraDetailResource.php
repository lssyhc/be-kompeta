<?php

namespace App\Http\Resources\Mitra;

use App\Models\CompanyProfile;
use App\Models\PartnershipProposal;
use App\Models\SchoolProfile;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class MitraDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        [
            $name,
            $logoUrl,
            $address,
            $sector,
            $employeeRange,
            $socials,
            $description,
            $gallery,
        ] = $this->resolveProfileFields();

        return [
            'id' => $this->id,
            'mitra_type' => $this->mitra_type,
            'name' => $name,
            'logo_url' => $logoUrl,
            'short_description' => $description,
            'address' => $address,
            'industry_sector' => $sector,
            'employee_total_range' => $employeeRange,
            'socials' => $socials,
            'gallery' => $gallery,
            'vacancy_count' => (int) ($this->vacancy_count ?? 0),
            'pengajuan_sekolah' => $this->formatPartnershipSubmissions(),
        ];
    }

    private function formatPartnershipSubmissions(): array
    {
        /** @var iterable<PartnershipProposal> $submissions */
        $submissions = $this->mitraPartnershipProposals ?? [];

        return collect($submissions)
            ->map(function (PartnershipProposal $proposal): array {
                $schoolUser = $proposal->schoolUser;
                $schoolName = null;
                $accreditation = null;

                if ($schoolUser instanceof User) {
                    $schoolProfile = $schoolUser->schoolProfile;

                    if ($schoolProfile instanceof SchoolProfile) {
                        $schoolName = $schoolProfile->school_name;
                        $accreditation = $schoolProfile->accreditation;
                    }

                    if (! is_string($schoolName) || $schoolName === '') {
                        $schoolName = $schoolUser->name;
                    }
                }

                return [
                    'school_user_id' => $proposal->school_user_id,
                    'nama_sekolah' => $schoolName,
                    'akreditasi' => $accreditation,
                    'tanggal_submit' => $proposal->submitted_at?->toIso8601String(),
                    'status_submit' => $proposal->status,
                ];
            })
            ->values()
            ->all();
    }

    private function resolveProfileFields(): array
    {
        if ($this->mitra_type === User::MITRA_PERUSAHAAN) {
            $profile = $this->companyProfile;
            if ($profile instanceof CompanyProfile) {
                return [
                    $profile->company_name,
                    $profile->company_logo_url,
                    $profile->office_address,
                    $profile->industry_sector,
                    $profile->employee_total_range,
                    $profile->socials,
                    $profile->short_description,
                    $this->buildGallery(
                        $profile->image_1_url,
                        $profile->image_2_url,
                        $profile->image_3_url,
                        $profile->image_4_url,
                        $profile->image_5_url,
                    ),
                ];
            }
        }

        if ($this->mitra_type === User::MITRA_UMKM) {
            $profile = $this->umkmProfile;
            if ($profile instanceof UmkmProfile) {
                return [
                    $profile->business_name,
                    $profile->umkm_logo_url,
                    $profile->business_address,
                    $profile->business_type,
                    null,
                    $profile->socials,
                    $profile->short_description,
                    $this->buildGallery(
                        $profile->image_1_url,
                        $profile->image_2_url,
                        $profile->image_3_url,
                        $profile->image_4_url,
                        $profile->image_5_url,
                    ),
                ];
            }
        }

        return [$this->name, null, null, null, null, null, null, []];
    }

    private function buildGallery(?string ...$urls): array
    {
        return array_values(array_filter($urls));
    }
}
