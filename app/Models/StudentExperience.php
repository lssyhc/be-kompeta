<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentExperience extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_profile_id',
        'title',
        'description',
        'position',
        'company_name',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }
}
