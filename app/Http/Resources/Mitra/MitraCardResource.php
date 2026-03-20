<?php

namespace App\Http\Resources\Mitra;

use App\Models\CompanyProfile;
use App\Models\JobVacancy;
use App\Models\UmkmProfile;
use App\Models\User;
use App\Support\ExploreFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/** @mixin User */
class MitraCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        [$name, $logoUrl, $address, $sector] = $this->resolveProfileFields();

        return [
            'id' => $this->id,
            'mitra_type' => $this->mitra_type,
            'name' => $name,
            'logo_url' => $logoUrl,
            'address' => $address,
            'industry_sector' => $sector,
            'vacancy_count' => (int) ($this->vacancy_count ?? 0),
            'last_active' => ExploreFormatter::relativeTime($this->last_login_at ? Carbon::parse($this->last_login_at) : null),
            'featured_vacancy' => $this->whenLoaded('featuredVacancy', function () {
                /** @var JobVacancy|null $v */
                $v = $this->featuredVacancy;

                if (! $v instanceof JobVacancy) {
                    return null;
                }

                return [
                    'slug' => $v->slug,
                    'position_name' => $v->position_name,
                    'salary_label' => ExploreFormatter::salaryLabel($v->salary_min, $v->salary_max, $v->is_salary_hidden),
                    'job_type_label' => $v->jobTypeLabel(),
                    'experience_level_label' => $v->experienceLevelLabel(),
                    'skills' => $v->skills->pluck('name')->values()->all(),
                ];
            }),
        ];
    }

    /**
     * @return array{0: ?string, 1: ?string, 2: ?string, 3: ?string}
     */
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
                ];
            }
        }

        return [$this->name, null, null, null];
    }
}
