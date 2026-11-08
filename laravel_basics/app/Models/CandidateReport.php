<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'report_type',
        'archetype_scores',
        'primary_archetype',
        'secondary_archetype',
        'employability_score',
        'narrative',
        'recommendations',
        'breakdown',
        'pdf_path',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'archetype_scores' => 'array',
            'employability_score' => 'float',
            'recommendations' => 'array',
            'breakdown' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the candidate user that owns the report.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
