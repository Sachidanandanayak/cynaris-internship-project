<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'skill',
        'category',
        'is_completed',
        'score',
        'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'score' => 'float',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
