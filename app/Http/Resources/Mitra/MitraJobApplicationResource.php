<?php

namespace App\Http\Resources\Mitra;

use App\Models\JobVacancy;
use App\Models\StudentApplication;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StudentApplication */
class MitraJobApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $studentProfile = $this->studentProfile;

        return [
            'id' => $this->id,
            'job_vacancy_id' => $this->job_vacancy_id,
            'job_slug' => $this->jobVacancy instanceof JobVacancy ? $this->jobVacancy->slug : null,
            'position_name' => $this->role_type,
            'status' => $this->status,
            'applied_at' => $this->applied_at?->toIso8601String(),
            'cover_letter' => $this->cover_letter,
            'has_cv_file' => is_string($this->cv_path) && $this->cv_path !== '',
            'student' => $studentProfile instanceof StudentProfile ? [
                'id' => $studentProfile->id,
                'full_name' => $studentProfile->full_name,
                'photo_profile_url' => $studentProfile->photo_profile_url,
                'school_origin' => $studentProfile->school_origin,
                'major' => $studentProfile->major,
                'graduation_status' => $studentProfile->graduation_status,
                'skills' => $this->whenLoaded('studentProfile', function () use ($studentProfile) {
                    return $studentProfile->relationLoaded('skills')
                        ? $studentProfile->skills->pluck('name')->values()->all()
                        : [];
                }, []),
            ] : null,
        ];
    }
}
