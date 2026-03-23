<?php

namespace App\Http\Resources\School;

use App\Models\CompanyProfile;
use App\Models\PartnershipProposal;
use App\Models\SchoolProfile;
use App\Models\StudentAchievement;
use App\Models\StudentProfile;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class SchoolDetailResource extends JsonResource
{
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
                'pengajuan_mitra' => [],
            ];
        }

        $gallery = array_values(array_filter([
            $profile->image_1_url,
            $profile->image_2_url,
            $profile->image_3_url,
            $profile->image_4_url,
            $profile->image_5_url,
        ]));

        $expertiseFields = $profile->getAttribute('expertise_fields');

        return [
            'id' => $this->id,
            'school_name' => $profile->school_name,
            'address' => $profile->address,
            'logo_url' => $profile->logo_url,
            'gallery' => $gallery,
            'short_description' => $profile->short_description,
            'accreditation' => $profile->accreditation,
            'expertise_fields' => is_array($expertiseFields)
                ? $expertiseFields
                : (json_decode((string) $expertiseFields, true) ?? []),
            'student_achievements' => $this->formatAchievements(),
            'students' => $this->formatStudents(),
            'pengajuan_mitra' => $this->formatPartnershipSubmissions(),
        ];
    }

    private function formatPartnershipSubmissions(): array
    {
        /** @var iterable<PartnershipProposal> $submissions */
        $submissions = $this->schoolPartnershipProposals ?? [];

        return collect($submissions)
            ->map(function (PartnershipProposal $proposal): array {
                $mitraUser = $proposal->mitraUser;
                $mitraName = null;
                $sectorOrType = null;

                if ($mitraUser instanceof User) {
                    if ($mitraUser->mitra_type === User::MITRA_PERUSAHAAN) {
                        $companyProfile = $mitraUser->companyProfile;

                        if ($companyProfile instanceof CompanyProfile) {
                            $mitraName = $companyProfile->company_name;
                            $sectorOrType = $companyProfile->industry_sector;
                        }
                    }

                    if ($mitraUser->mitra_type === User::MITRA_UMKM) {
                        $umkmProfile = $mitraUser->umkmProfile;

                        if ($umkmProfile instanceof UmkmProfile) {
                            $mitraName = $umkmProfile->business_name;
                            $sectorOrType = $umkmProfile->business_type;
                        }
                    }

                    if (! is_string($mitraName) || $mitraName === '') {
                        $mitraName = $mitraUser->name;
                    }
                }

                return [
                    'mitra_user_id' => $proposal->mitra_user_id,
                    'nama_mitra' => $mitraName,
                    'sektor_atau_tipe' => $sectorOrType,
                    'mitra_tipe' => $mitraUser instanceof User ? $mitraUser->mitra_type : null,
                    'tanggal_submit' => $proposal->submitted_at?->toIso8601String(),
                    'status_submit' => $proposal->status,
                ];
            })
            ->values()
            ->all();
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
