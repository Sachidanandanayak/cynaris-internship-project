<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlacementReadinessScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_completion_score',
        'assessment_score',
        'cia_usage_score',
        'documentation_score',
        'placement_readiness_score',
        'viva_score',
        'code_review_score',
        'demo_clarity_score',
        'git_quality_score',
        'interview_readiness_score',
        'breakdown',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'project_completion_score' => 'float',
            'assessment_score' => 'float',
            'cia_usage_score' => 'float',
            'documentation_score' => 'float',
            'placement_readiness_score' => 'float',
            'viva_score' => 'float',
            'code_review_score' => 'float',
            'demo_clarity_score' => 'float',
            'git_quality_score' => 'float',
            'interview_readiness_score' => 'float',
            'breakdown' => 'array',
        ];
    }

    /**
     * Get the candidate user that owns the readiness score.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
