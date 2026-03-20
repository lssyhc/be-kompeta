<?php

namespace App\Http\Resources\Explore;

use App\Models\JobVacancy;
use App\Support\ExploreFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin JobVacancy */
class ExploreJobCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'position_name' => $this->position_name,
            'job_type' => $this->job_type,
            'job_type_label' => $this->jobTypeLabel(),
            'work_policy' => $this->work_policy,
            'work_policy_label' => $this->workPolicyLabel(),
            'created_at_human' => ExploreFormatter::relativeTime($this->created_at),
            'salary_label' => ExploreFormatter::salaryLabel($this->salary_min, $this->salary_max, $this->is_salary_hidden),
            'mitra_name' => $this->resolveMitraName(),
            'mitra_type' => $this->resolveMitraType(),
            'province' => $this->province,
            'location' => $this->resolveMitraAddress() ?? $this->province,
            'skills' => $this->skills->pluck('name')->values()->all(),
            'benefits' => $this->benefits->pluck('name')->values()->all(),
            'mitra_logo_url' => $this->resolveMitraLogoUrl(),
        ];
    }
}
