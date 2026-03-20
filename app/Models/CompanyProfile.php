<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CompanyProfile extends Model
{
    use HasFactory;

    protected $appends = [
        'company_logo_url',
        'image_1_url',
        'image_2_url',
        'image_3_url',
        'image_4_url',
        'image_5_url',
    ];

    protected $fillable = [
        'user_id',
        'company_name',
        'nib',
        'industry_sector',
        'employee_total_range',
        'office_address',
        'website_or_social_url',
        'short_description',
        'company_logo_path',
        'image_1_path',
        'image_2_path',
        'image_3_path',
        'image_4_path',
        'image_5_path',
        'kemenkumham_decree_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCompanyLogoUrlAttribute(): ?string
    {
        if (! $this->company_logo_path) {
            return null;
        }

        if (str_starts_with($this->company_logo_path, 'http://') || str_starts_with($this->company_logo_path, 'https://')) {
            return $this->company_logo_path;
        }

        return Storage::disk('public')->url($this->company_logo_path);
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
