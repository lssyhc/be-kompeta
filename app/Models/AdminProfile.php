<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AdminProfile extends Model
{
    use HasFactory;

    protected $appends = [
        'avatar_url',
    ];

    protected $fillable = [
        'user_id',
        'full_name',
        'avatar_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }

        if (str_starts_with($this->avatar_path, 'http://') || str_starts_with($this->avatar_path, 'https://')) {
            return $this->avatar_path;
        }

        return Storage::disk('public')->url($this->avatar_path);
    }
}
