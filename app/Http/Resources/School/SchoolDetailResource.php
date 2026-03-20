<?php

namespace App\Http\Resources\School;

use App\Models\SchoolProfile;
use App\Models\StudentAchievement;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class SchoolDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profile = $this->schoolProfile;

        if (! $profile instanceof SchoolProfile) {
            return [
                'id' => $this->id,
                'school_name' => $this->name,
                'address' => null,
                'logo_url' => null,
                'gallery' => [],
                'short_description' => null,
                'accreditation' => null,
                'expertise_fields' => [],
                'student_achievements' => [],
                'students' => [],
            ];
        }

        $gallery = array_values(array_filter([
            $profile->image_1_url,
            $profile->image_2_url,
            $profile->image_3_url,
            $profile->image_4_url,
            $profile->image_5_url,
        ]));

        return [
            'id' => $this->id,
            'school_name' => $profile->school_name,
            'address' => $profile->address,
            'logo_url' => $profile->logo_url,
            'gallery' => $gallery,
            'short_description' => $profile->short_description,
            'accreditation' => $profile->accreditation,
            'expertise_fields' => json_decode($profile->expertise_fields, true) ?? [],
            'student_achievements' => $this->formatAchievements(),
            'students' => $this->formatStudents(),
        ];
    }

    private function formatAchievements(): array
    {
        $achievements = [];

        /** @var iterable<StudentProfile> $students */
        $students = $this->managedStudents ?? [];

        foreach ($students as $student) {
            $studentAchievements = $student->achievements;

            /** @var iterable<StudentAchievement> $studentAchievements */
            foreach ($studentAchievements as $achievement) {
                $achievements[] = [
                    'title' => $achievement->title,
                    'description' => $achievement->description,
                ];
            }
        }

        return $achievements;
    }

    private function formatStudents(): array
    {
        /** @var iterable<StudentProfile> $students */
        $students = $this->managedStudents ?? [];

        return collect($students)
            ->map(function (StudentProfile $student): array {
                return [
                    'id' => $student->id,
                    'full_name' => $student->full_name,
                    'major' => $student->major,
                    'graduation_status' => $student->graduation_status,
                    'photo_profile_url' => $student->photo_profile_url,
                ];
            })
            ->values()
            ->all();
    }
}
