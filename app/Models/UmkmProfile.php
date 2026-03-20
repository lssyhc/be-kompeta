<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UmkmProfile extends Model
{
    use HasFactory;

    protected $appends = [
        'umkm_logo_url',
        'image_1_url',
        'image_2_url',
        'image_3_url',
        'image_4_url',
        'image_5_url',
    ];

    protected $fillable = [
        'user_id',
        'business_name',
        'owner_nik',
        'owner_personal_nib',
        'business_type',
        'business_address',
        'umkm_logo_path',
        'owner_ktp_photo_path',
        'short_description',
        'image_1_path',
        'image_2_path',
        'image_3_path',
        'image_4_path',
        'image_5_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUmkmLogoUrlAttribute(): ?string
    {
        return $this->umkm_logo_path ? Storage::disk('public')->url($this->umkm_logo_path) : null;
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
