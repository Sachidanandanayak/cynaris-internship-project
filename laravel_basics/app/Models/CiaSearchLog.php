<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CiaSearchLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'target_role',
        'query_type',
        'skill_profile',
        'gaps',
        'readiness_score',
        'priority_learning',
        'recommendations',
        'status',
        'metadata',
    ];

    protected $casts = [
        'skill_profile' => 'array',
        'gaps' => 'array',
        'readiness_score' => 'float',
        'priority_learning' => 'array',
        'recommendations' => 'array',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
