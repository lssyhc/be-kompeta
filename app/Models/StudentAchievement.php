<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_profile_id',
        'title',
        'description',
        'achievement_date',
        'institution_name',
    ];

    protected function casts(): array
    {
        return [
            'achievement_date' => 'date',
        ];
    }

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }
}
