<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property Carbon|null $submitted_at */
class PartnershipProposal extends Model
{
    use HasFactory;

    public const STATUS_SUBMITTED = 'submitted';

    protected $fillable = [
        'proposer_user_id',
        'target_user_id',
        'school_user_id',
        'mitra_user_id',
        'proposal_pdf_path',
        'signature_path',
        'notes',
        'status',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function proposerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposer_user_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function schoolUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'school_user_id');
    }

    public function mitraUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mitra_user_id');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }
}
