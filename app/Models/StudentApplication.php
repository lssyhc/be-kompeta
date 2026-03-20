<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_profile_id',
        'company_name',
        'role_type',
        'submitted_at',
        'submit_status',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'date',
        ];
    }

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }
}
