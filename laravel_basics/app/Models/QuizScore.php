<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quiz_title',
        'skill',
        'score',
        'max_score',
        'percentage',
        'passed',
        'attempted_at',
    ];

    protected $casts = [
        'score' => 'float',
        'max_score' => 'float',
        'percentage' => 'float',
        'passed' => 'boolean',
        'attempted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
