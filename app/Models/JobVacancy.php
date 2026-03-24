<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobVacancy extends Model
{
    use HasFactory;

    public const JOB_TYPE_FULL_TIME = 'penuh_waktu';

    public const JOB_TYPE_CONTRACT = 'kontrak';

    public const JOB_TYPE_INTERNSHIP = 'magang';

    public const JOB_TYPE_PART_TIME = 'paruh_waktu';

    public const JOB_TYPE_FREELANCE = 'freelance';

    public const WORK_POLICY_OFFICE = 'kantor';

    public const WORK_POLICY_HYBRID = 'hybrid';

    public const WORK_POLICY_REMOTE = 'remote';

    public const EXPERIENCE_NO_EXPERIENCE = 'tidak_berpengalaman';

    public const EXPERIENCE_FRESH_GRADUATE = 'fresh_graduate';

    public const EXPERIENCE_LESS_THAN_ONE_YEAR = 'kurang_dari_setahun';

    protected $fillable = [
        'mitra_user_id',
        'slug',
        'position_name',
        'category',
        'job_type',
        'work_policy',
        'experience_level',
        'province',
        'salary_min',
        'salary_max',
        'is_salary_hidden',
        'requirements',
        'job_description',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_salary_hidden' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function mitraUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mitra_user_id');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(JobVacancySkill::class);
    }

    public function benefits(): HasMany
    {
        return $this->hasMany(JobVacancyBenefit::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public static function jobTypeLabels(): array
    {
        return [
            self::JOB_TYPE_FULL_TIME => 'Penuh Waktu',
            self::JOB_TYPE_CONTRACT => 'Kontrak',
            self::JOB_TYPE_INTERNSHIP => 'Magang',
            self::JOB_TYPE_PART_TIME => 'Paruh Waktu',
            self::JOB_TYPE_FREELANCE => 'Freelance',
        ];
    }

    public static function workPolicyLabels(): array
    {
        return [
            self::WORK_POLICY_OFFICE => 'Kerja di kantor',
            self::WORK_POLICY_HYBRID => 'Kerja di kantor / rumah',
            self::WORK_POLICY_REMOTE => 'Kerja remote/dari rumah',
        ];
    }

    public static function experienceLabels(): array
    {
        return [
            self::EXPERIENCE_NO_EXPERIENCE => 'Tidak berpengalaman',
            self::EXPERIENCE_FRESH_GRADUATE => 'Fresh Graduate',
            self::EXPERIENCE_LESS_THAN_ONE_YEAR => 'Kurang dari setahun',
        ];
    }

    public function jobTypeLabel(): string
    {
        return self::jobTypeLabels()[$this->job_type] ?? $this->job_type;
    }

    public function workPolicyLabel(): string
    {
        return self::workPolicyLabels()[$this->work_policy] ?? $this->work_policy;
    }

    public function experienceLevelLabel(): string
    {
        return self::experienceLabels()[$this->experience_level] ?? $this->experience_level;
    }

    public function resolveMitraName(): ?string
    {
        $mitraUser = $this->mitraUser;

        if (! $mitraUser instanceof User) {
            return null;
        }

        if ($mitraUser->mitra_type === User::MITRA_PERUSAHAAN) {
            $companyProfile = $this->resolveCompanyProfile($mitraUser);

            return $companyProfile instanceof CompanyProfile
                ? $companyProfile->company_name
                : $mitraUser->name;
        }

        if ($mitraUser->mitra_type === User::MITRA_UMKM) {
            $umkmProfile = $this->resolveUmkmProfile($mitraUser);

            return $umkmProfile instanceof UmkmProfile
                ? $umkmProfile->business_name
                : $mitraUser->name;
        }

        return $mitraUser->name;
    }

    public function resolveMitraAddress(): ?string
    {
        $mitraUser = $this->mitraUser;

        if (! $mitraUser instanceof User) {
            return null;
        }

        if ($mitraUser->mitra_type === User::MITRA_PERUSAHAAN) {
            return $this->resolveCompanyProfile($mitraUser)?->office_address;
        }

        if ($mitraUser->mitra_type === User::MITRA_UMKM) {
            return $this->resolveUmkmProfile($mitraUser)?->business_address;
        }

        return null;
    }

    public function resolveMitraSector(): ?string
    {
        $mitraUser = $this->mitraUser;

        if (! $mitraUser instanceof User) {
            return null;
        }

        if ($mitraUser->mitra_type === User::MITRA_PERUSAHAAN) {
            return $this->resolveCompanyProfile($mitraUser)?->industry_sector;
        }

        if ($mitraUser->mitra_type === User::MITRA_UMKM) {
            return $this->resolveUmkmProfile($mitraUser)?->business_type;
        }

        return null;
    }

    public function resolveMitraEmployeeRange(): ?string
    {
        $mitraUser = $this->mitraUser;

        if (! $mitraUser instanceof User) {
            return null;
        }

        if ($mitraUser->mitra_type === User::MITRA_PERUSAHAAN) {
            return $this->resolveCompanyProfile($mitraUser)?->employee_total_range;
        }

        return null;
    }

    public function resolveMitraDescription(): ?string
    {
        $mitraUser = $this->mitraUser;

        if (! $mitraUser instanceof User) {
            return null;
        }

        if ($mitraUser->mitra_type === User::MITRA_PERUSAHAAN) {
            return $this->resolveCompanyProfile($mitraUser)?->short_description;
        }

        if ($mitraUser->mitra_type === User::MITRA_UMKM) {
            return $this->resolveUmkmProfile($mitraUser)?->short_description;
        }

        return null;
    }

    public function resolveMitraSocials(): ?array
    {
        $mitraUser = $this->mitraUser;

        if (! $mitraUser instanceof User) {
            return null;
        }

        if ($mitraUser->mitra_type === User::MITRA_PERUSAHAAN) {
            /** @var array|null */
            return $this->resolveCompanyProfile($mitraUser)?->socials;
        }

        if ($mitraUser->mitra_type === User::MITRA_UMKM) {
            /** @var array|null */
            return $this->resolveUmkmProfile($mitraUser)?->socials;
        }

        return null;
    }

    public function resolveMitraLogoUrl(): ?string
    {
        $mitraUser = $this->mitraUser;

        if (! $mitraUser instanceof User) {
            return null;
        }

        if ($mitraUser->mitra_type === User::MITRA_PERUSAHAAN) {
            return $this->resolveCompanyProfile($mitraUser)?->company_logo_url;
        }

        if ($mitraUser->mitra_type === User::MITRA_UMKM) {
            return $this->resolveUmkmProfile($mitraUser)?->umkm_logo_url;
        }

        return null;
    }

    public function resolveMitraType(): ?string
    {
        $mitraUser = $this->mitraUser;

        return $mitraUser instanceof User
            ? $mitraUser->mitra_type
            : null;
    }

    private function resolveCompanyProfile(User $mitraUser): ?CompanyProfile
    {
        $companyProfile = $mitraUser->companyProfile()->first();

        return $companyProfile instanceof CompanyProfile
            ? $companyProfile
            : null;
    }

    private function resolveUmkmProfile(User $mitraUser): ?UmkmProfile
    {
        $umkmProfile = $mitraUser->umkmProfile()->first();

        return $umkmProfile instanceof UmkmProfile
            ? $umkmProfile
            : null;
    }
}
