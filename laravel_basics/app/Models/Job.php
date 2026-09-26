<?php

namespace App\Models;

use Database\Factories\JobFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    /** @use HasFactory<JobFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'jobs';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'company',
        'location',
        'employment_type',
        'description',
        'requirements',
        'salary_range',
        'application_deadline',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'application_deadline' => 'date',
        ];
    }

    /**
     * The user / recruiter who posted the job.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Applications submitted for this job.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Scope query to only active jobs.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope query for search and filtering.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('requirements', 'like', "%{$search}%");
                });
            })
            ->when($filters['employment_type'] ?? null, function ($query, $type) {
                if ($type !== 'all' && !empty($type)) {
                    $query->where('employment_type', $type);
                }
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                if ($status !== 'all' && !empty($status)) {
                    $query->where('status', $status);
                }
            });
    }

    /**
     * Check if a specific user has already applied for this job.
     */
    public function hasAppliedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->applications()->where('user_id', $user->id)->exists();
    }
}
