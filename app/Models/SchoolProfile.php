<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SchoolProfile extends Model
{
    use HasFactory;

    protected $appends = [
        'logo_url',
        'image_1_url',
        'image_2_url',
        'image_3_url',
        'image_4_url',
        'image_5_url',
    ];

    protected $fillable = [
        'user_id',
        'school_name',
        'npsn',
        'accreditation',
        'address',
        'socials',
        'expertise_fields',
        'logo_path',
        'image_1_path',
        'image_2_path',
        'image_3_path',
        'image_4_path',
        'image_5_path',
        'short_description',
        'operational_license_path',
    ];

    public const DEFAULT_SOCIALS = [
        'website' => null,
        'instagram' => null,
        'linkedin' => null,
        'whatsapp' => null,
    ];

    protected function casts(): array
    {
        return [
            'expertise_fields' => 'array',
            'socials' => 'array',
        ];
    }

    public function getSocialsAttribute(?string $value): array
    {
        $decoded = $value ? json_decode($value, true) : [];

        return array_merge(self::DEFAULT_SOCIALS, $decoded ?? []);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        if (str_starts_with($this->logo_path, 'http://') || str_starts_with($this->logo_path, 'https://')) {
            return $this->logo_path;
        }

        return Storage::disk('public')->url($this->logo_path);
    }

    public function getImage1UrlAttribute(): ?string
    {
        if (! $this->image_1_path) {
            return null;
        }

        if (str_starts_with($this->image_1_path, 'http://') || str_starts_with($this->image_1_path, 'https://')) {
            return $this->image_1_path;
        }

        return Storage::disk('public')->url($this->image_1_path);
    }

    public function getImage2UrlAttribute(): ?string
    {
        if (! $this->image_2_path) {
            return null;
        }

        if (str_starts_with($this->image_2_path, 'http://') || str_starts_with($this->image_2_path, 'https://')) {
            return $this->image_2_path;
        }

        return Storage::disk('public')->url($this->image_2_path);
    }

    public function getImage3UrlAttribute(): ?string
    {
        if (! $this->image_3_path) {
            return null;
        }

        if (str_starts_with($this->image_3_path, 'http://') || str_starts_with($this->image_3_path, 'https://')) {
            return $this->image_3_path;
        }

        return Storage::disk('public')->url($this->image_3_path);
    }

    public function getImage4UrlAttribute(): ?string
    {
        if (! $this->image_4_path) {
            return null;
        }

        if (str_starts_with($this->image_4_path, 'http://') || str_starts_with($this->image_4_path, 'https://')) {
            return $this->image_4_path;
        }

        return Storage::disk('public')->url($this->image_4_path);
    }

    public function getImage5UrlAttribute(): ?string
    {
        if (! $this->image_5_path) {
            return null;
        }

        if (str_starts_with($this->image_5_path, 'http://') || str_starts_with($this->image_5_path, 'https://')) {
            return $this->image_5_path;
        }

        return Storage::disk('public')->url($this->image_5_path);
    }
}
