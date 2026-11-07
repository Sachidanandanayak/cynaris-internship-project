<?php

namespace App\Http\Controllers;

use App\Models\CiaSearchLog;
use App\Models\Job;
use App\Services\LearningPathGeneratorService;
use App\Services\Readiness\PlacementReadinessService;
use App\Services\Resume\ResumeAtsScoringService;
use App\Services\Resume\ResumeTextExtractor;
use App\Services\SkillGapAnalysisService;
use App\Services\Vector\ChromaDbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CiaController extends Controller
{
    public function __construct(
        protected SkillGapAnalysisService $gapService,
        protected ChromaDbService $chromaService,
        protected LearningPathGeneratorService $learningPathService,
        protected ResumeTextExtractor $textExtractor,
        protected ResumeAtsScoringService $atsScoringService,
        protected PlacementReadinessService $readinessService
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

        $latestLearningPath = $user
            ? $user->ciaSearchLogs()->where('query_type', 'learning_path')->latest()->first()
            : null;

        $latestReadiness = $user
            ? $this->readinessService->getOrCalculateLatest($user)
            : null;

        // Calculate initial candidate readiness score if lessons exist
        $initialAnalysis = $user
            ? $this->gapService->analyze($user, $skillProfile, $defaultRole)
            : [
                'score' => 0.0,
                'gaps' => [],
                'priority_learning' => [],
                'engine' => 'initial_state',
            ];

        // Retrieve initial top-5 semantic internship matches (W9D4)
        $initialMatches = [];
        $vectorSyncStatus = [
            'is_synchronized' => false,
            'last_synced_at' => null,
            'target_collection' => 'cynaris_intern_profiles',
        ];

        if ($user) {
            try {
                $matchResponse = $this->chromaService->matchInternships($user, ['limit' => 5]);
                $initialMatches = $matchResponse['data'] ?? [];
                $vectorSyncStatus = [
                    'is_synchronized' => true,
                    'last_synced_at' => now()->toIso8601String(),
                    'target_collection' => 'cynaris_intern_profiles',
                ];
            } catch (\Throwable $e) {
                Log::warning('Failed retrieving initial semantic internship matches: ' . $e->getMessage());
            }
        }

        return view('career.index', [
            'candidate' => $user,
            'completedLessons' => $completedLessons,
            'quizScores' => $quizScores,
            'skillProfile' => $skillProfile,
            'defaultRole' => $defaultRole,
            'radarData' => $radarData,
            'initialAnalysis' => $initialAnalysis,
            'initialMatches' => $initialMatches,
            'vectorSyncStatus' => $vectorSyncStatus,
            'latestLearningPath' => $latestLearningPath,
            'latestReadiness' => $latestReadiness,
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

    /**
     * POST /candidate/cia/career/profile/sync
     *
     * Synchronizes candidate profile to ChromaDB vector store (cynaris_intern_profiles).
     * Enforces zero PII in vector document narrative and metadata.
     */
    public function syncProfile(Request $request): JsonResponse
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
            'force_refresh' => ['nullable', 'boolean'],
            'sync_source' => ['nullable', 'string', 'in:user_manual_trigger,assessment_completion,scheduled_cron'],
        ]);

        try {
            $syncResult = $this->chromaService->syncProfile($user, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Candidate vector profile synchronized successfully.',
                'data' => [
                    'sync_job_id' => 'job_sync_' . $user->id . '_' . date('YmdHis'),
                    'candidate_id' => $syncResult['candidate_id'] ?? ('cand_' . $user->id),
                    'status' => 'SYNCHRONIZED',
                    'target_collection' => $syncResult['target_collection'] ?? 'cynaris_intern_profiles',
                    'estimated_completion_seconds' => 1,
                    'queued_at' => now()->toIso8601String(),
                    'last_synced_at' => $syncResult['last_synced_at'] ?? now()->toIso8601String(),
                ],
            ], 202);
        } catch (\Throwable $e) {
            Log::error('Profile vector sync error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during vector profile synchronization.',
                'error_code' => 'ERR_SYNC_FAILED',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET /candidate/cia/career/matches
     *
     * Queries ChromaDB for top semantic internship matches with domain, affinity, and location filters.
     */
    public function matches(Request $request): JsonResponse
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
            'domain' => ['nullable', 'string', 'max:50'],
            'min_affinity' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
            'location_type' => ['nullable', 'string', 'max:50'],
        ]);

        try {
            $result = $this->chromaService->matchInternships($user, $validated);

            return response()->json($result, 200);
        } catch (\Throwable $e) {
            Log::error('ChromaDB internship matching error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'filters' => $validated,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during semantic internship matching.',
                'error_code' => 'ERR_MATCHING_FAILED',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /candidate/cia/career/learning-path
     *
     * Generates an Eight-Week Personalized Learning Path via LangGraph 5-node StateGraph (W9D5).
     * Nodes: AssessState -> IdentifyGaps -> BuildPlan -> ValidatePlan -> OutputPlan
     */
    public function learningPath(Request $request): JsonResponse
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
            'target_job_id' => ['nullable', 'integer', 'exists:jobs,id'],
        ]);

        $targetRole = trim($validated['target_role'] ?? 'Junior Backend Laravel Engineer') ?: 'Junior Backend Laravel Engineer';
        $jobId = !empty($validated['target_job_id']) ? (int) $validated['target_job_id'] : null;

        try {
            $result = $this->learningPathService->generateLearningPath($user, $targetRole, $jobId);

            return response()->json($result, 200);
        } catch (\Throwable $e) {
            Log::error('LangGraph learning path generation error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'target_role' => $targetRole,
                'target_job_id' => $jobId,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during learning path generation.',
                'error_code' => 'ERR_LEARNING_PATH_FAILED',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /candidate/cia/career/resume/scan
     *
     * Uploads candidate PDF resume, extracts raw text, and generates Groq ATS score breakdown (W10D1).
     */
    public function scanResume(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Candidate is unauthenticated.',
                'error_code' => 'ERR_UNAUTHENTICATED',
            ], 401);
        }

        $request->validate([
            'resume' => ['required', 'file', 'mimes:pdf', 'max:10240'], // 10MB limit
            'target_role' => ['nullable', 'string', 'max:150'],
            'job_id' => ['nullable', 'integer', 'exists:jobs,id'],
        ]);

        $file = $request->file('resume');
        $targetRole = trim((string) $request->input('target_role', 'Junior Backend Laravel Engineer')) ?: 'Junior Backend Laravel Engineer';
        $jobId = $request->input('job_id') ? (int) $request->input('job_id') : null;
        $job = $jobId ? Job::find($jobId) : null;

        try {
            $realPath = $file->getRealPath();
            $extractedText = $this->textExtractor->extract($realPath);

            if (empty(trim($extractedText))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to extract text from the provided PDF resume. Ensure the PDF contains readable text streams and is not an image scan or encrypted.',
                    'error_code' => 'ERR_PDF_EXTRACTION_EMPTY',
                ], 422);
            }

            $scoringResult = $this->atsScoringService->scoreResume($user, $extractedText, $targetRole, $job);

            return response()->json(array_merge([
                'success' => true,
                'message' => 'Resume analyzed and ATS scores calculated successfully.',
                'character_count' => strlen($extractedText),
                'word_count' => str_word_count($extractedText),
            ], $scoringResult), 200);
        } catch (\Throwable $e) {
            Log::error('Resume ATS scanning failure: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'target_role' => $targetRole,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during resume ATS scoring: ' . $e->getMessage(),
                'error_code' => 'ERR_RESUME_SCAN_FAILED',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /candidate/cia/career/readiness/calculate
     *
     * Evaluates Placement Readiness and Interview Readiness scores (W10D2).
     */
    public function calculateReadiness(Request $request): JsonResponse
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
            'project_completion_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'assessment_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'cia_usage_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'documentation_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'viva_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'code_review_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'demo_clarity_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'git_quality_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $readinessRecord = $this->readinessService->calculateScores($user, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Placement and Interview readiness scores evaluated successfully.',
                'data' => $readinessRecord,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Placement readiness calculation error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while evaluating readiness scores.',
                'error_code' => 'ERR_READINESS_CALCULATION_FAILED',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET /candidate/cia/career/readiness
     *
     * Returns the latest evaluated Placement and Interview readiness scorecard (W10D2).
     */
    public function getReadiness(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Candidate is unauthenticated.',
                'error_code' => 'ERR_UNAUTHENTICATED',
            ], 401);
        }

        try {
            $latest = $this->readinessService->getOrCalculateLatest($user);

            return response()->json([
                'success' => true,
                'data' => $latest,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Readiness retrieval error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve candidate readiness score.',
                'error_code' => 'ERR_READINESS_FETCH_FAILED',
            ], 500);
        }
    }
}
