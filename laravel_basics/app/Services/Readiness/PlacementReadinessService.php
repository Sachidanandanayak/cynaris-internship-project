<?php

namespace App\Services\Readiness;

use App\Models\PlacementReadinessScore;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PlacementReadinessService
{
    /**
     * Placement Readiness Metric Weights:
     * - Project Completion: 40%
     * - Assessment (Quiz/Exams): 30%
     * - CIA Usage: 10%
     * - Documentation: 20%
     */
    public const PLACEMENT_WEIGHTS = [
        'project_completion' => 0.40,
        'assessment' => 0.30,
        'cia_usage' => 0.10,
        'documentation' => 0.20,
    ];

    /**
     * Interview Readiness Metric Weights:
     * - Viva: 40%
     * - Code Review: 30%
     * - Demo Clarity: 20%
     * - Git Quality: 10%
     */
    public const INTERVIEW_WEIGHTS = [
        'viva' => 0.40,
        'code_review' => 0.30,
        'demo_clarity' => 0.20,
        'git_quality' => 0.10,
    ];

    /**
     * Calculate and persist readiness scores for a given candidate user.
     *
     * @param User $user
     * @param array<string, mixed> $overrides
     * @return PlacementReadinessScore
     */
    public function calculateScores(User $user, array $overrides = []): PlacementReadinessScore
    {
        // 1. Placement Readiness Component Scores
        $projectCompletion = isset($overrides['project_completion_score'])
            ? (float) $overrides['project_completion_score']
            : $this->deriveProjectCompletionScore($user);

        $assessment = isset($overrides['assessment_score'])
            ? (float) $overrides['assessment_score']
            : $this->deriveAssessmentScore($user);

        $ciaUsage = isset($overrides['cia_usage_score'])
            ? (float) $overrides['cia_usage_score']
            : $this->deriveCiaUsageScore($user);

        $documentation = isset($overrides['documentation_score'])
            ? (float) $overrides['documentation_score']
            : (float) ($overrides['documentation'] ?? 80.0);

        // Clamp components to [0, 100]
        $projectCompletion = $this->clampScore($projectCompletion);
        $assessment = $this->clampScore($assessment);
        $ciaUsage = $this->clampScore($ciaUsage);
        $documentation = $this->clampScore($documentation);

        // Exact Placement Readiness Formula:
        // (Project Completion * 0.40) + (Assessment * 0.30) + (CIA Usage * 0.10) + (Documentation * 0.20)
        $placementReadinessScore = round(
            ($projectCompletion * self::PLACEMENT_WEIGHTS['project_completion']) +
            ($assessment * self::PLACEMENT_WEIGHTS['assessment']) +
            ($ciaUsage * self::PLACEMENT_WEIGHTS['cia_usage']) +
            ($documentation * self::PLACEMENT_WEIGHTS['documentation']),
            1
        );
        $placementReadinessScore = $this->clampScore($placementReadinessScore);

        // 2. Interview Readiness Component Scores
        $viva = isset($overrides['viva_score'])
            ? (float) $overrides['viva_score']
            : $this->deriveVivaScore($user, $assessment);

        $codeReview = isset($overrides['code_review_score'])
            ? (float) $overrides['code_review_score']
            : (float) ($overrides['code_review'] ?? 82.0);

        $demoClarity = isset($overrides['demo_clarity_score'])
            ? (float) $overrides['demo_clarity_score']
            : (float) ($overrides['demo_clarity'] ?? 78.0);

        $gitQuality = isset($overrides['git_quality_score'])
            ? (float) $overrides['git_quality_score']
            : (float) ($overrides['git_quality'] ?? 85.0);

        // Clamp components to [0, 100]
        $viva = $this->clampScore($viva);
        $codeReview = $this->clampScore($codeReview);
        $demoClarity = $this->clampScore($demoClarity);
        $gitQuality = $this->clampScore($gitQuality);

        // Exact Interview Readiness Formula:
        // (Viva * 0.40) + (Code Review * 0.30) + (Demo Clarity * 0.20) + (Git Quality * 0.10)
        $interviewReadinessScore = round(
            ($viva * self::INTERVIEW_WEIGHTS['viva']) +
            ($codeReview * self::INTERVIEW_WEIGHTS['code_review']) +
            ($demoClarity * self::INTERVIEW_WEIGHTS['demo_clarity']) +
            ($gitQuality * self::INTERVIEW_WEIGHTS['git_quality']),
            1
        );
        $interviewReadinessScore = $this->clampScore($interviewReadinessScore);

        // Build informative breakdown payload
        $breakdown = [
            'placement' => [
                'formula' => '(Project Completion * 0.40) + (Assessment * 0.30) + (CIA Usage * 0.10) + (Documentation * 0.20)',
                'weights' => self::PLACEMENT_WEIGHTS,
                'metrics' => [
                    'project_completion' => [
                        'score' => $projectCompletion,
                        'weight' => 0.40,
                        'contribution' => round($projectCompletion * 0.40, 2),
                    ],
                    'assessment' => [
                        'score' => $assessment,
                        'weight' => 0.30,
                        'contribution' => round($assessment * 0.30, 2),
                    ],
                    'cia_usage' => [
                        'score' => $ciaUsage,
                        'weight' => 0.10,
                        'contribution' => round($ciaUsage * 0.10, 2),
                    ],
                    'documentation' => [
                        'score' => $documentation,
                        'weight' => 0.20,
                        'contribution' => round($documentation * 0.20, 2),
                    ],
                ],
                'final_score' => $placementReadinessScore,
                'status_tier' => $this->resolveStatusTier($placementReadinessScore),
            ],
            'interview' => [
                'formula' => '(Viva * 0.40) + (Code Review * 0.30) + (Demo Clarity * 0.20) + (Git Quality * 0.10)',
                'weights' => self::INTERVIEW_WEIGHTS,
                'metrics' => [
                    'viva' => [
                        'score' => $viva,
                        'weight' => 0.40,
                        'contribution' => round($viva * 0.40, 2),
                    ],
                    'code_review' => [
                        'score' => $codeReview,
                        'weight' => 0.30,
                        'contribution' => round($codeReview * 0.30, 2),
                    ],
                    'demo_clarity' => [
                        'score' => $demoClarity,
                        'weight' => 0.20,
                        'contribution' => round($demoClarity * 0.20, 2),
                    ],
                    'git_quality' => [
                        'score' => $gitQuality,
                        'weight' => 0.10,
                        'contribution' => round($gitQuality * 0.10, 2),
                    ],
                ],
                'final_score' => $interviewReadinessScore,
                'status_tier' => $this->resolveStatusTier($interviewReadinessScore),
            ],
            'evaluated_at' => now()->toIso8601String(),
        ];

        $notes = $overrides['notes'] ?? 'Automated placement and interview readiness evaluation based on coursework, assessments, and CIA telemetry.';

        // Persist to database
        $record = PlacementReadinessScore::create([
            'user_id' => $user->id,
            'project_completion_score' => $projectCompletion,
            'assessment_score' => $assessment,
            'cia_usage_score' => $ciaUsage,
            'documentation_score' => $documentation,
            'placement_readiness_score' => $placementReadinessScore,
            'viva_score' => $viva,
            'code_review_score' => $codeReview,
            'demo_clarity_score' => $demoClarity,
            'git_quality_score' => $gitQuality,
            'interview_readiness_score' => $interviewReadinessScore,
            'breakdown' => $breakdown,
            'status' => 'evaluated',
            'notes' => $notes,
        ]);

        return $record;
    }

    /**
     * Retrieve the latest readiness score for a candidate or compute one if none exists.
     *
     * @param User $user
     * @return PlacementReadinessScore
     */
    public function getOrCalculateLatest(User $user): PlacementReadinessScore
    {
        $latest = $user->latestPlacementReadinessScore;
        if ($latest) {
            return $latest;
        }

        return $this->calculateScores($user);
    }

    /**
     * Derive project completion score from candidate lessons.
     */
    protected function deriveProjectCompletionScore(User $user): float
    {
        $completedLessons = $user->candidateLessons()->where('is_completed', true)->get();
        $count = $completedLessons->count();

        if ($count === 0) {
            return 60.0;
        }

        $avgScore = (float) ($completedLessons->avg('score') ?: 85.0);
        // Minimum score scales with completion count (up to 10 lessons = full completion base)
        $completionVolumeFactor = min(1.0, $count / 8.0);

        return round(min(100.0, max(40.0, ($avgScore * 0.7) + ($completionVolumeFactor * 100.0 * 0.3))), 1);
    }

    /**
     * Derive assessment score from candidate quizzes.
     */
    protected function deriveAssessmentScore(User $user): float
    {
        $quizScores = $user->quizScores()->get();
        if ($quizScores->isEmpty()) {
            return 65.0;
        }

        return round((float) $quizScores->avg('percentage'), 1);
    }

    /**
     * Derive CIA platform usage score from activity telemetry logs.
     */
    protected function deriveCiaUsageScore(User $user): float
    {
        $logCount = $user->ciaSearchLogs()->count();
        if ($logCount === 0) {
            return 50.0;
        }

        // Each interaction adds to readiness up to 100%
        return round(min(100.0, max(50.0, 50.0 + ($logCount * 10.0))), 1);
    }

    /**
     * Derive viva examination score.
     */
    protected function deriveVivaScore(User $user, float $assessmentScore): float
    {
        // Correlated with assessment baseline
        return round(min(100.0, max(40.0, ($assessmentScore * 0.9) + 5.0)), 1);
    }

    /**
     * Clamp score strictly between 0.0 and 100.0.
     */
    public function clampScore(float $value): float
    {
        return (float) min(100.0, max(0.0, $value));
    }

    /**
     * Map numeric score to human-readable status badge.
     */
    public function resolveStatusTier(float $score): string
    {
        return match (true) {
            $score >= 85.0 => 'Placement Ready (Exceptional)',
            $score >= 70.0 => 'Interview Ready (Proficient)',
            $score >= 50.0 => 'Needs Improvement (Developing)',
            default => 'Foundational (Action Required)',
        };
    }
}

