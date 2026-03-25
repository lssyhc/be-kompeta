<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class StudentProfile extends Model
{
    use HasFactory;

    protected $appends = [
        'photo_profile_url',
    ];

    public const DEFAULT_SOCIALS = [
        'website' => null,
        'instagram' => null,
        'linkedin' => null,
        'whatsapp' => null,
    ];

    protected $fillable = [
        'user_id',
        'school_user_id',
        'full_name',
        'nisn',
        'photo_profile_path',
        'major',
        'school_origin',
        'graduation_status',
        'class_year',
        'unique_code',
        'description',
        'socials',
        'address',
    ];

    protected function casts(): array
    {
        return [
            'socials' => 'array',
        ];
    }

    public function getSocialsAttribute(?string $value): array
    {
        $decoded = $value ? json_decode($value, true) : [];

        return array_merge(self::DEFAULT_SOCIALS, $decoded ?? []);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(User::class, 'school_user_id');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(StudentSkill::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(StudentExperience::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(StudentAchievement::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(StudentApplication::class);
    }

    public function getPhotoProfileUrlAttribute(): ?string
    {
        if (! $this->photo_profile_path) {
            return null;
        }

        if (str_starts_with($this->photo_profile_path, 'http://') || str_starts_with($this->photo_profile_path, 'https://')) {
            return $this->photo_profile_path;
        }

        return Storage::disk('public')->url($this->photo_profile_path);
    }
}
