<?php

namespace App\Http\Resources\ForYou;

use App\Models\JobVacancy;
use App\Models\User;
use App\Support\ExploreFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin JobVacancy */
class ForYouJobDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $frontendBaseUrl = rtrim((string) (config('app.frontend_url') ?: config('app.url')), '/');

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'position_name' => $this->position_name,
            'mitra_name' => $this->resolveMitraName(),
            'mitra_type' => $this->resolveMitraType(),
            'mitra_logo_url' => $this->resolveMitraLogoUrl(),
            'job_type' => $this->job_type,
            'job_type_label' => $this->jobTypeLabel(),
            'work_policy' => $this->work_policy,
            'work_policy_label' => $this->workPolicyLabel(),
            'created_at_human' => ExploreFormatter::relativeTime($this->created_at),
            'updated_at_human' => ExploreFormatter::relativeTime($this->updated_at),
            'share_link' => $frontendBaseUrl.'/for-you/'.$this->slug,
            'requirements' => $this->requirements,
            'skills' => $this->skills->pluck('name')->values()->all(),
            'work_benefits' => $this->benefits->pluck('name')->values()->all(),
            'managed_by' => $this->resolveMitraName(),
            'job_description' => $this->job_description,
            'location' => $this->resolveMitraAddress() ?? $this->province,
            'province' => $this->province,
            'about_company' => [
                'name' => $this->resolveMitraName(),
                'industry_sector' => $this->resolveMitraSector(),
                'employee_total_range' => $this->resolveMitraEmployeeRange(),
                'description' => $this->resolveMitraDescription(),
                'address' => $this->resolveMitraAddress(),
                'website_or_social_url' => $this->resolveMitraWebsiteOrSocialUrl(),
                'logo_url' => $this->resolveMitraLogoUrl(),
                'category_label' => $this->resolveMitraType() === User::MITRA_UMKM ? 'UMKM' : 'Perusahaan',
            ],
        ];
    }
}
