<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'phone', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the jobs posted by this user.
     */
    public function jobs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Job::class);
    }

    /**
     * Get the job applications submitted by this user.
     */
    public function applications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Get candidate completed lessons.
     */
    public function candidateLessons(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CandidateLesson::class);
    }

    /**
     * Get candidate quiz scores.
     */
    public function quizScores(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(QuizScore::class);
    }

    /**
     * Get candidate CIA search / gap analysis logs.
     */
    public function ciaSearchLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CiaSearchLog::class);
    }

    /**
     * Build candidate validated skill-profile array from completed lessons and quiz scores.
     *
     * @return array<int, array{skill: string, proficiency_score: float, proficiency_level: string, lessons_completed: int, quiz_average: float, verified: bool}>
     */
    public function buildSkillProfile(): array
    {
        $completedLessons = $this->candidateLessons()
            ->where('is_completed', true)
            ->get();

        $quizScores = $this->quizScores()->get();

        // Unique skills present across lessons and quizzes
        $lessonSkills = $completedLessons->pluck('skill')->filter()->unique();
        $quizSkills = $quizScores->pluck('skill')->filter()->unique();
        $allSkills = $lessonSkills->merge($quizSkills)->unique()->values();

        if ($allSkills->isEmpty()) {
            return [];
        }

        $profile = [];
        foreach ($allSkills as $skill) {
            $skillLessons = $completedLessons->where('skill', $skill);
            $skillQuizzes = $quizScores->where('skill', $skill);

            $lessonCount = $skillLessons->count();
            $quizCount = $skillQuizzes->count();

            $quizAvg = $quizCount > 0 ? round((float) $skillQuizzes->avg('percentage'), 1) : 0.0;
            $lessonAvg = $lessonCount > 0 ? round((float) $skillLessons->avg('score') ?: 80.0, 1) : 0.0;

            if ($quizCount > 0 && $lessonCount > 0) {
                $proficiencyScore = round(($quizAvg * 0.7) + ($lessonAvg * 0.3), 1);
            } elseif ($quizCount > 0) {
                $proficiencyScore = $quizAvg;
            } else {
                $proficiencyScore = round(min(100.0, $lessonCount * 25.0), 1);
            }

            $level = match (true) {
                $proficiencyScore >= 80.0 => 'Advanced',
                $proficiencyScore >= 60.0 => 'Intermediate',
                default => 'Beginner',
            };

            $profile[] = [
                'skill' => (string) $skill,
                'proficiency_score' => (float) $proficiencyScore,
                'proficiency_level' => $level,
                'lessons_completed' => (int) $lessonCount,
                'quiz_average' => (float) $quizAvg,
                'verified' => $quizCount > 0 && $quizAvg >= 70.0,
            ];
        }

        // Sort descending by proficiency score
        usort($profile, fn ($a, $b) => $b['proficiency_score'] <=> $a['proficiency_score']);

        return $profile;
    }
}
