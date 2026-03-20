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

    protected function casts(): array
    {
        return [
            'expertise_fields' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    public function getImage1UrlAttribute(): ?string
    {
        return $this->image_1_path ? Storage::disk('public')->url($this->image_1_path) : null;
    }

    public function getImage2UrlAttribute(): ?string
    {
        return $this->image_2_path ? Storage::disk('public')->url($this->image_2_path) : null;
    }

    public function getImage3UrlAttribute(): ?string
    {
        return $this->image_3_path ? Storage::disk('public')->url($this->image_3_path) : null;
    }

    public function getImage4UrlAttribute(): ?string
    {
        return $this->image_4_path ? Storage::disk('public')->url($this->image_4_path) : null;
    }

    public function getImage5UrlAttribute(): ?string
    {
        return $this->image_5_path ? Storage::disk('public')->url($this->image_5_path) : null;
    }
}
