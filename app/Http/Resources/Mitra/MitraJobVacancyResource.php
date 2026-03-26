<?php

namespace App\Http\Resources\Mitra;

use App\Models\JobVacancy;
use App\Support\ExploreFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin JobVacancy */
class MitraJobVacancyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'position_name' => $this->position_name,
            'category' => $this->category,
            'job_type' => $this->job_type,
            'job_type_label' => $this->jobTypeLabel(),
            'work_policy' => $this->work_policy,
            'work_policy_label' => $this->workPolicyLabel(),
            'experience_level' => $this->experience_level,
            'experience_level_label' => $this->experienceLevelLabel(),
            'province' => $this->province,
            'salary_min' => $this->salary_min,
            'salary_max' => $this->salary_max,
            'is_salary_hidden' => $this->is_salary_hidden,
            'salary_label' => ExploreFormatter::salaryLabel($this->salary_min, $this->salary_max, $this->is_salary_hidden),
            'requirements' => $this->requirements,
            'job_description' => $this->job_description,
            'is_published' => $this->is_published,
            'skills' => $this->skills->pluck('name')->values()->all(),
            'benefits' => $this->benefits->pluck('name')->values()->all(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
