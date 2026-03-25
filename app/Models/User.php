<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_SEKOLAH = 'sekolah';

    public const ROLE_MITRA = 'mitra';

    public const ROLE_SISWA = 'siswa';

    public const MITRA_PERUSAHAAN = 'perusahaan';

    public const MITRA_UMKM = 'umkm';

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_REJECTED = 'rejected';

    public const DEFAULT_PROFILE_PHOTO_PATH = 'defaults/profile-picture.png';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'mitra_type',
        'account_status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    public function schoolProfile(): HasOne
    {
        return $this->hasOne(SchoolProfile::class);
    }

    public function companyProfile(): HasOne
    {
        return $this->hasOne(CompanyProfile::class);
    }

    public function umkmProfile(): HasOne
    {
        return $this->hasOne(UmkmProfile::class);
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function adminProfile(): HasOne
    {
        return $this->hasOne(AdminProfile::class);
    }

    public function managedStudents(): HasMany
    {
        return $this->hasMany(StudentProfile::class, 'school_user_id');
    }

    public function jobVacancies(): HasMany
    {
        return $this->hasMany(JobVacancy::class, 'mitra_user_id');
    }

    public function featuredVacancy(): HasOne
    {
        return $this->hasOne(JobVacancy::class, 'mitra_user_id')
            ->where('is_published', true)
            ->latest();
    }

    public function proposedPartnershipProposals(): HasMany
    {
        return $this->hasMany(PartnershipProposal::class, 'proposer_user_id');
    }

    public function receivedPartnershipProposals(): HasMany
    {
        return $this->hasMany(PartnershipProposal::class, 'target_user_id');
    }

    public function schoolPartnershipProposals(): HasMany
    {
        return $this->hasMany(PartnershipProposal::class, 'school_user_id');
    }

    public function createdArticles(): HasMany
    {
        return $this->hasMany(Article::class, 'created_by');
    }

    public function updatedArticles(): HasMany
    {
        return $this->hasMany(Article::class, 'updated_by');
    }

    public function bookmarkedJobVacancies(): BelongsToMany
    {
        return $this->belongsToMany(JobVacancy::class, 'job_vacancy_bookmarks')
            ->withPivot('created_at');
    }
}
