<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Carbon|null $applied_at
 */
class StudentApplication extends Model
{
    use HasFactory;

    public const STATUS_SUBMITTED = 'submitted';

    protected $fillable = [
        'student_profile_id',
        'job_vacancy_id',
        'mitra_user_id',
        'company_name',
        'role_type',
        'cv_path',
        'cover_letter',
        'status',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'applied_at' => 'datetime',
        ];
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function jobVacancy(): BelongsTo
    {
        return $this->belongsTo(JobVacancy::class);
    }

    public function mitraUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mitra_user_id');
    }
}
