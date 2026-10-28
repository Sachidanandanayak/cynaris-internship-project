<?php

namespace App\Http\Controllers;

use App\Models\CiaSearchLog;
use App\Services\SkillGapAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CiaController extends Controller
{
    public function __construct(
        protected SkillGapAnalysisService $gapService
    ) {}

    /**
     * Display the CIA Career Intelligence Cockpit tab.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $completedLessons = $user ? $user->candidateLessons()->where('is_completed', true)->latest()->get() : collect();
        $quizScores = $user ? $user->quizScores()->latest()->get() : collect();
        $skillProfile = $user ? $user->buildSkillProfile() : [];

        $defaultRole = 'Junior Backend Laravel Engineer';
        $benchmark = $this->gapService->resolveBenchmark($defaultRole);

        $candidateMap = [];
        foreach ($skillProfile as $item) {
            $candidateMap[$item['skill']] = (float) $item['proficiency_score'];
        }
        $radarData = $this->gapService->buildBenchmarkComparison($candidateMap, $benchmark);

        $recentLogs = $user
            ? $user->ciaSearchLogs()->latest()->take(5)->get()
            : collect();

        // Calculate initial candidate readiness score if lessons exist
        $initialAnalysis = $user
            ? $this->gapService->analyze($user, $skillProfile, $defaultRole)
            : [
                'score' => 0.0,
                'gaps' => [],
                'priority_learning' => [],
                'engine' => 'initial_state',
            ];

        return view('career.index', [
            'candidate' => $user,
            'completedLessons' => $completedLessons,
            'quizScores' => $quizScores,
            'skillProfile' => $skillProfile,
            'defaultRole' => $defaultRole,
            'radarData' => $radarData,
            'initialAnalysis' => $initialAnalysis,
            'recentLogs' => $recentLogs,
        ]);
    }

    /**
     * POST /candidate/cia/career/skill-gap
     *
     * Analyzes candidate completed coursework and quiz performance against target role.
     */
    public function skillGap(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Candidate is unauthenticated.',
                'error_code' => 'ERR_UNAUTHENTICATED',
            ], 401);
        }

        $validated = $request->validate([
            'target_role' => ['required', 'string', 'min:2', 'max:150'],
        ]);

        $targetRole = trim($validated['target_role']);

        try {
            // Retrieve candidate's completed lessons & quiz scores
            $skillProfile = $user->buildSkillProfile();

            // Perform gap analysis via Groq / resilient pipeline
            $analysis = $this->gapService->analyze($user, $skillProfile, $targetRole);

            // Persist to cia_search_logs
            $log = CiaSearchLog::create([
                'user_id' => $user->id,
                'target_role' => $analysis['target_role'],
                'query_type' => 'skill_gap',
                'skill_profile' => $skillProfile,
                'gaps' => $analysis['gaps'],
                'readiness_score' => $analysis['score'],
                'priority_learning' => $analysis['priority_learning'],
                'status' => 'completed',
                'metadata' => [
                    'engine' => $analysis['engine'],
                    'radar' => $analysis['benchmark_comparison'] ?? null,
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);

            return response()->json([
                'success' => true,
                'target_role' => $analysis['target_role'],
                'score' => (float) $analysis['score'],
                'gaps' => $analysis['gaps'],
                'priority_learning' => $analysis['priority_learning'],
                'skill_profile' => $skillProfile,
                'radar' => $analysis['benchmark_comparison'] ?? null,
                'engine' => $analysis['engine'],
                'log_id' => $log->id,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Skill gap analysis error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'target_role' => $targetRole,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during skill gap analysis. Please try again.',
                'error_code' => 'ERR_SKILL_GAP_FAILED',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /candidate/cia/career/recommendations
     *
     * Generates 3 career recommendations via CrewAI Agent (W9D3).
     */
    public function recommendations(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Candidate is unauthenticated.',
                'error_code' => 'ERR_UNAUTHENTICATED',
            ], 401);
        }

        $validated = $request->validate([
            'target_role' => ['nullable', 'string', 'max:150'],
        ]);

        $targetRole = trim($validated['target_role'] ?? 'Junior Backend Laravel Engineer') ?: 'Junior Backend Laravel Engineer';

        try {
            $skillProfile = $user->buildSkillProfile();
            $analysis = $this->gapService->analyze($user, $skillProfile, $targetRole);

            // Invoke Career Recommendation Service
            $recommendationService = app(\App\Services\CareerRecommendationService::class);
            $recommendations = $recommendationService->generateRecommendations($user, $skillProfile, $analysis['gaps'], $targetRole);

            // Persist to cia_search_logs
            $log = CiaSearchLog::create([
                'user_id' => $user->id,
                'target_role' => $targetRole,
                'query_type' => 'career_recommendation',
                'skill_profile' => $skillProfile,
                'gaps' => $analysis['gaps'],
                'readiness_score' => $analysis['score'],
                'recommendations' => $recommendations['paths'] ?? [],
                'status' => 'completed',
                'metadata' => [
                    'agent' => $recommendations['agent'] ?? 'CareerAdvisorAgent',
                    'engine' => $recommendations['engine'] ?? 'crewai',
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);

            return response()->json([
                'success' => true,
                'target_role' => $targetRole,
                'recommendations' => $recommendations['paths'] ?? [],
                'agent' => $recommendations['agent'] ?? 'CareerAdvisorAgent',
                'engine' => $recommendations['engine'] ?? 'crewai',
                'log_id' => $log->id,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Career recommendation error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'target_role' => $targetRole,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during career recommendation generation.',
                'error_code' => 'ERR_RECOMMENDATION_FAILED',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
