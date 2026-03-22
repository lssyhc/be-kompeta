<?php

namespace App\Http\Resources\Student;

use App\Models\JobVacancy;
use App\Models\StudentApplication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StudentApplication */
class StudentJobApplicationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_profile_id' => $this->student_profile_id,
            'job_vacancy_id' => $this->job_vacancy_id,
            'job_slug' => $this->jobVacancy instanceof JobVacancy ? $this->jobVacancy->slug : null,
            'mitra_user_id' => $this->mitra_user_id,
            'company_name' => $this->company_name,
            'role_type' => $this->role_type,
            'status_submit' => $this->status,
            'submitted_at' => $this->submitted_at?->toDateString(),
            'applied_at' => $this->applied_at?->toIso8601String(),
            'cover_letter' => $this->cover_letter,
            'has_cv_file' => is_string($this->cv_path) && $this->cv_path !== '',
        ];
    }
}
