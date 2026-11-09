<?php

namespace App\Services\CareerReport;

use App\Models\CandidateReport;
use App\Models\CiaSearchLog;
use App\Models\User;
use App\Services\Readiness\PlacementReadinessService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CareerIntelligenceReportService
{
    /**
     * Archetype metadata and definitions.
     */
    public const ARCHETYPES = [
        'Builder' => [
            'name' => 'Builder',
            'tagline' => 'Systems Architect & Craftsperson',
            'description' => 'Focused on robust system architecture, database schema design, high-throughput APIs, and production engineering.',
            'badge_color' => 'indigo',
        ],
        'Analyst' => [
            'name' => 'Analyst',
            'tagline' => 'Data-Driven & Algorithmic Problem Solver',
            'description' => 'Excels in methodical decomposition, automated testing rigor, performance profiling, and structural debugging.',
            'badge_color' => 'blue',
        ],
        'Leader' => [
            'name' => 'Leader',
            'tagline' => 'Technical Communicator & Architectural Champion',
            'description' => 'Bridges high-level vision and execution, articulating trade-offs, mentoring peers, and upholding engineering culture.',
            'badge_color' => 'purple',
        ],
        'Innovator' => [
            'name' => 'Innovator',
            'tagline' => 'Adaptive Pioneer & AI Integrator',
            'description' => 'Rapidly tests and implements emerging paradigms including agentic workflows, vector embeddings, and LLM pipelines.',
            'badge_color' => 'amber',
        ],
        'Specialist' => [
            'name' => 'Specialist',
            'tagline' => 'Deep Domain Authority',
            'description' => 'Possesses authoritative vertical mastery in core backend engineering, relational internals, and Laravel ecosystem mechanics.',
            'badge_color' => 'emerald',
        ],
        'Generalist' => [
            'name' => 'Generalist',
            'tagline' => 'Full-Spectrum Swiss Army Knife',
            'description' => 'Effortlessly bridges backend, frontend, DevOps, documentation, and quality assurance across the full delivery cycle.',
            'badge_color' => 'teal',
        ],
    ];

    /**
     * Employability score calculation weights:
     * - Primary Archetype Score: 25%
     * - Placement Readiness: 35%
     * - Interview Readiness: 25%
     * - Skill Profile Average: 15%
     */
    public const EMPLOYABILITY_WEIGHTS = [
        'primary_archetype' => 0.25,
        'placement_readiness' => 0.35,
        'interview_readiness' => 0.25,
        'skill_average' => 0.15,
    ];

    public function __construct(
        protected PlacementReadinessService $readinessService
    ) {}

    /**
     * Generate, score, and persist a complete Career Intelligence Report for candidate.
     *
     * @param User $candidate
     * @param array<string, mixed> $options
     * @return CandidateReport
     */
    public function generateReport(User $candidate, array $options = []): CandidateReport
    {
        // 1. Retrieve readiness scores & skill profile
        $readiness = $this->readinessService->getOrCalculateLatest($candidate);
        $skillProfile = $candidate->buildSkillProfile();

        // 2. Compute 6 Archetype Scores
        $archetypeScores = $this->calculateArchetypeScores($candidate, $readiness, $skillProfile);

        // Sort archetypes descending to resolve primary and secondary
        arsort($archetypeScores);
        $archetypeKeys = array_keys($archetypeScores);
        $primaryArchetype = $archetypeKeys[0] ?? 'Builder';
        $secondaryArchetype = $archetypeKeys[1] ?? 'Analyst';

        // 3. Calculate Explainable Employability Score
        $employability = $this->calculateEmployabilityScore(
            $archetypeScores[$primaryArchetype],
            $readiness->placement_readiness_score,
            $readiness->interview_readiness_score,
            $skillProfile
        );

        // 4. Generate AI Narrative with Groq (or deterministic heuristic fallback)
        $narrativeData = $this->generateNarrative(
            $candidate,
            $primaryArchetype,
            $secondaryArchetype,
            $archetypeScores,
            $employability['score'],
            $readiness,
            $skillProfile
        );

        // Combine breakdown metadata for explainability
        $breakdown = [
            'employability' => $employability,
            'archetype_scores' => $archetypeScores,
            'archetype_metadata' => self::ARCHETYPES,
            'readiness_summary' => [
                'placement_score' => $readiness->placement_readiness_score,
                'interview_score' => $readiness->interview_readiness_score,
                'placement_tier' => $this->readinessService->resolveStatusTier($readiness->placement_readiness_score),
            ],
            'narrative_details' => $narrativeData,
            'skill_count' => count($skillProfile),
            'verified_skill_count' => count(array_filter($skillProfile, fn ($s) => !empty($s['verified']))),
        ];

        // 5. Persist CandidateReport
        $report = CandidateReport::create([
            'user_id' => $candidate->id,
            'report_type' => 'career_intelligence',
            'archetype_scores' => $archetypeScores,
            'primary_archetype' => $primaryArchetype,
            'secondary_archetype' => $secondaryArchetype,
            'employability_score' => $employability['score'],
            'narrative' => $narrativeData['executive_summary'] . "\n\n" . $narrativeData['archetype_analysis'],
            'recommendations' => $narrativeData['strengths'] + $narrativeData['growth_areas'],
            'breakdown' => $breakdown,
            'metadata' => [
                'engine' => $narrativeData['engine'] ?? 'deterministic_fallback',
                'generated_at' => now()->toIso8601String(),
                'roadmap' => $narrativeData['roadmap_90_days'] ?? [],
                'hiring_pitch' => $narrativeData['hiring_pitch'] ?? '',
                'strengths' => $narrativeData['strengths'] ?? [],
                'growth_areas' => $narrativeData['growth_areas'] ?? [],
            ],
        ]);

        // 6. Generate and store PDF file
        try {
            $pdfPath = $this->generateAndStorePdf($report, $candidate, $breakdown);
            $report->update(['pdf_path' => $pdfPath]);
        } catch (\Throwable $e) {
            Log::warning("DomPDF generation skipped during report creation: {$e->getMessage()}");
        }

        // 7. Audit Log in cia_search_logs
        CiaSearchLog::create([
            'user_id' => $candidate->id,
            'target_role' => $primaryArchetype . ' Archetype Assessment',
            'query_type' => 'career_intelligence_report',
            'readiness_score' => $employability['score'],
            'skill_profile' => $skillProfile,
            'recommendations' => $narrativeData['strengths'] ?? [],
            'status' => 'completed',
            'metadata' => [
                'report_id' => $report->id,
                'primary_archetype' => $primaryArchetype,
                'secondary_archetype' => $secondaryArchetype,
                'employability_score' => $employability['score'],
            ],
        ]);

        return $report;
    }

    /**
     * Compute candidate scores for all 6 engineering archetypes (0.0 to 100.0).
     *
     * @param User $candidate
     * @param mixed $readiness
     * @param array $skillProfile
     * @return array<string, float>
     */
    public function calculateArchetypeScores(User $candidate, $readiness, array $skillProfile): array
    {
        $projectScore = (float) $readiness->project_completion_score;
        $assessmentScore = (float) $readiness->assessment_score;
        $ciaScore = (float) $readiness->cia_usage_score;
        $docScore = (float) $readiness->documentation_score;
        $vivaScore = (float) $readiness->viva_score;
        $codeReviewScore = (float) $readiness->code_review_score;
        $demoScore = (float) $readiness->demo_clarity_score;
        $gitScore = (float) $readiness->git_quality_score;

        // Skill stats
        $skillScores = array_column($skillProfile, 'proficiency_score');
        $topSkillScore = !empty($skillScores) ? (float) max($skillScores) : 60.0;
        $skillAvg = !empty($skillScores) ? (float) (array_sum($skillScores) / count($skillScores)) : 60.0;
        $skillCount = count($skillProfile);

        // 1. Builder: Project completion, code craftsmanship, git hygiene
        $builderScore = ($projectScore * 0.40) + ($codeReviewScore * 0.30) + ($topSkillScore * 0.20) + ($gitScore * 0.10);

        // 2. Analyst: Quiz assessment precision, code review testing, telemetry
        $analystScore = ($assessmentScore * 0.45) + ($codeReviewScore * 0.25) + ($skillAvg * 0.20) + ($ciaScore * 0.10);

        // 3. Leader: Viva defense, demo clarity, documentation, git quality
        $leaderScore = ($vivaScore * 0.40) + ($demoScore * 0.30) + ($docScore * 0.20) + ($gitScore * 0.10);

        // 4. Innovator: CIA usage telemetry depth, query diversity, project adaptability
        $telemetryDiversityBonus = min(20.0, $candidate->ciaSearchLogs()->distinct('query_type')->count() * 4.0);
        $innovatorScore = ($ciaScore * 0.40) + ($projectScore * 0.25) + ($telemetryDiversityBonus * 1.5) + ($assessmentScore * 0.05);

        // 5. Specialist: Peak domain skill score, verified quiz mastery, project depth
        $verifiedCount = count(array_filter($skillProfile, fn ($s) => !empty($s['verified'])));
        $specialistBonus = min(15.0, $verifiedCount * 3.0);
        $specialistScore = ($topSkillScore * 0.50) + ($assessmentScore * 0.25) + ($projectScore * 0.10) + $specialistBonus;

        // 6. Generalist: Cross-domain skill count diversity, balanced readiness average
        $diversityFactor = min(100.0, max(40.0, $skillCount * 12.5));
        $readinessAverage = ($readiness->placement_readiness_score + $readiness->interview_readiness_score) / 2.0;
        $generalistScore = ($diversityFactor * 0.40) + ($readinessAverage * 0.40) + ($docScore * 0.10) + ($demoScore * 0.10);

        return [
            'Builder' => $this->clampScore($builderScore),
            'Analyst' => $this->clampScore($analystScore),
            'Leader' => $this->clampScore($leaderScore),
            'Innovator' => $this->clampScore($innovatorScore),
            'Specialist' => $this->clampScore($specialistScore),
            'Generalist' => $this->clampScore($generalistScore),
        ];
    }

    /**
     * Calculate explainable Employability Score with full sub-component breakdown.
     *
     * @param float $primaryArchetypeScore
     * @param float $placementScore
     * @param float $interviewScore
     * @param array $skillProfile
     * @return array{score: float, status_tier: string, weights: array, components: array, formula: string}
     */
    public function calculateEmployabilityScore(
        float $primaryArchetypeScore,
        float $placementScore,
        float $interviewScore,
        array $skillProfile
    ): array {
        $skillScores = array_column($skillProfile, 'proficiency_score');
        $skillAverage = !empty($skillScores) ? (float) (array_sum($skillScores) / count($skillScores)) : 60.0;

        $pArchetypeWeighted = $primaryArchetypeScore * self::EMPLOYABILITY_WEIGHTS['primary_archetype'];
        $placementWeighted = $placementScore * self::EMPLOYABILITY_WEIGHTS['placement_readiness'];
        $interviewWeighted = $interviewScore * self::EMPLOYABILITY_WEIGHTS['interview_readiness'];
        $skillWeighted = $skillAverage * self::EMPLOYABILITY_WEIGHTS['skill_average'];

        $compositeScore = $this->clampScore($pArchetypeWeighted + $placementWeighted + $interviewWeighted + $skillWeighted);

        $statusTier = match (true) {
            $compositeScore >= 85.0 => 'Placement Ready (Tier 1 Priority)',
            $compositeScore >= 70.0 => 'Competitive Candidate (Tier 2 Ready)',
            $compositeScore >= 50.0 => 'In Development (Tier 3 Progressing)',
            default => 'Foundational (Tier 4 Focus Required)',
        };

        return [
            'score' => $compositeScore,
            'status_tier' => $statusTier,
            'weights' => self::EMPLOYABILITY_WEIGHTS,
            'components' => [
                'primary_archetype' => [
                    'raw' => round($primaryArchetypeScore, 1),
                    'weighted' => round($pArchetypeWeighted, 2),
                    'weight_pct' => '25%',
                ],
                'placement_readiness' => [
                    'raw' => round($placementScore, 1),
                    'weighted' => round($placementWeighted, 2),
                    'weight_pct' => '35%',
                ],
                'interview_readiness' => [
                    'raw' => round($interviewScore, 1),
                    'weighted' => round($interviewWeighted, 2),
                    'weight_pct' => '25%',
                ],
                'skill_average' => [
                    'raw' => round($skillAverage, 1),
                    'weighted' => round($skillWeighted, 2),
                    'weight_pct' => '15%',
                ],
            ],
            'formula' => 'Employability = (PrimaryArchetype * 0.25) + (PlacementReadiness * 0.35) + (InterviewReadiness * 0.25) + (SkillAverage * 0.15)',
        ];
    }

    /**
     * Generate Career Intelligence Narrative via Groq or fallback.
     */
    protected function generateNarrative(
        User $candidate,
        string $primaryArchetype,
        string $secondaryArchetype,
        array $archetypeScores,
        float $employabilityScore,
        $readiness,
        array $skillProfile
    ): array {
        $apiKey = config('services.groq.key');

        if (!empty($apiKey)) {
            try {
                $groqData = $this->queryGroq(
                    $candidate,
                    $primaryArchetype,
                    $secondaryArchetype,
                    $archetypeScores,
                    $employabilityScore,
                    $readiness,
                    $skillProfile
                );

                if ($groqData !== null && $this->validateNarrativePayload($groqData)) {
                    $groqData['engine'] = 'groq_llama_3_3_70b';
                    return $groqData;
                }
            } catch (\Throwable $e) {
                Log::warning("Groq career report generation failed: {$e->getMessage()}. Attempting OpenRouter secondary fallback.");
            }
        }

        // Secondary fallback: OpenRouter API
        $openRouterKey = config('services.openrouter.key');
        if (!empty($openRouterKey)) {
            try {
                $openRouterData = $this->queryOpenRouter(
                    $candidate,
                    $primaryArchetype,
                    $secondaryArchetype,
                    $archetypeScores,
                    $employabilityScore,
                    $readiness,
                    $skillProfile
                );

                if ($openRouterData !== null && $this->validateNarrativePayload($openRouterData)) {
                    $openRouterData['engine'] = 'openrouter_llama_3_3_70b';
                    return $openRouterData;
                }
            } catch (\Throwable $e) {
                Log::warning("OpenRouter career report generation failed: {$e->getMessage()}. Invoking deterministic fallback.");
            }
        }

        return $this->deterministicNarrativeFallback(
            $candidate,
            $primaryArchetype,
            $secondaryArchetype,
            $archetypeScores,
            $employabilityScore,
            $readiness,
            $skillProfile
        );
    }

    /**
     * Query Groq Chat Completions API with structured JSON output.
     */
    protected function queryGroq(
        User $candidate,
        string $primaryArchetype,
        string $secondaryArchetype,
        array $archetypeScores,
        float $employabilityScore,
        $readiness,
        array $skillProfile
    ): ?array {
        $url = config('services.groq.url', 'https://api.groq.com/openai/v1/chat/completions');
        $model = config('services.groq.model', 'llama-3.3-70b-versatile');
        $apiKey = config('services.groq.key');

        $systemPrompt = <<<PROMPT
You are the Cynaris Intelligence Agent (CIA) Career Analyst.
Generate an executive Career Intelligence Report for a software engineering candidate.
You MUST output valid, parseable JSON only. Do not include markdown codeblocks or preamble.

JSON Schema:
{
  "executive_summary": "2-3 sentences evaluating overall employability, key strengths, and role fit",
  "archetype_analysis": "2-3 sentences examining the interplay between primary and secondary archetypes",
  "strengths": ["string", "string", "string"],
  "growth_areas": ["string", "string", "string"],
  "roadmap_90_days": [
    {"phase": "Month 1 (Days 1-30)", "timeframe": "Days 1-30", "objective": "string", "key_actions": ["string", "string"]},
    {"phase": "Month 2 (Days 31-60)", "timeframe": "Days 31-60", "objective": "string", "key_actions": ["string", "string"]},
    {"phase": "Month 3 (Days 61-90)", "timeframe": "Days 61-90", "objective": "string", "key_actions": ["string", "string"]}
  ],
  "hiring_pitch": "2-sentence compelling pitch for technical recruiters and hiring managers"
}
PROMPT;

        $userPrompt = "Candidate: {$candidate->name}\n" .
            "Employability Score: {$employabilityScore}%\n" .
            "Primary Archetype: {$primaryArchetype} ({$archetypeScores[$primaryArchetype]}%)\n" .
            "Secondary Archetype: {$secondaryArchetype} ({$archetypeScores[$secondaryArchetype]}%)\n" .
            "Archetype Breakdown: " . json_encode($archetypeScores) . "\n" .
            "Placement Readiness: {$readiness->placement_readiness_score}%\n" .
            "Interview Readiness: {$readiness->interview_readiness_score}%\n" .
            "Verified Skills: " . json_encode(array_slice($skillProfile, 0, 6));

        $response = Http::withToken($apiKey)
            ->timeout(10)
            ->retry(2, 100, throw: false)
            ->post($url, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object'],
            ]);

        if (!$response->successful()) {
            return null;
        }

        $body = $response->json();
        $content = $body['choices'][0]['message']['content'] ?? null;
        if (empty($content)) {
            return null;
        }

        $clean = trim($content);
        if (str_starts_with($clean, '```')) {
            $clean = preg_replace('/^```(?:json)?/i', '', $clean);
            $clean = preg_replace('/```$/', '', $clean);
            $clean = trim($clean);
        }

        $decoded = json_decode($clean, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Query OpenRouter API as resilient secondary provider.
     */
    protected function queryOpenRouter(
        User $candidate,
        string $primaryArchetype,
        string $secondaryArchetype,
        array $archetypeScores,
        float $employabilityScore,
        $readiness,
        array $skillProfile
    ): ?array {
        $endpoint = config('services.openrouter.endpoint', 'https://openrouter.ai/api/v1/chat/completions');
        $model = config('services.openrouter.model', 'meta-llama/llama-3.3-70b-instruct');
        $apiKey = config('services.openrouter.key');
        $timeout = (int) config('services.openrouter.timeout', 15);

        $systemPrompt = <<<PROMPT
You are the Cynaris Intelligence Agent (CIA) Career Analyst.
Generate an executive Career Intelligence Report for a software engineering candidate.
You MUST output valid, parseable JSON only. Do not include markdown codeblocks or preamble.

JSON Schema:
{
  "executive_summary": "2-3 sentences evaluating overall employability, key strengths, and role fit",
  "archetype_analysis": "2-3 sentences examining the interplay between primary and secondary archetypes",
  "strengths": ["string", "string", "string"],
  "growth_areas": ["string", "string", "string"],
  "roadmap_90_days": [
    {"phase": "Month 1 (Days 1-30)", "timeframe": "Days 1-30", "objective": "string", "key_actions": ["string", "string"]},
    {"phase": "Month 2 (Days 31-60)", "timeframe": "Days 31-60", "objective": "string", "key_actions": ["string", "string"]},
    {"phase": "Month 3 (Days 61-90)", "timeframe": "Days 61-90", "objective": "string", "key_actions": ["string", "string"]}
  ],
  "hiring_pitch": "2-sentence compelling pitch for technical recruiters and hiring managers"
}
PROMPT;

        $userPrompt = "Candidate: {$candidate->name}\n" .
            "Employability Score: {$employabilityScore}%\n" .
            "Primary Archetype: {$primaryArchetype} ({$archetypeScores[$primaryArchetype]}%)\n" .
            "Secondary Archetype: {$secondaryArchetype} ({$archetypeScores[$secondaryArchetype]}%)\n" .
            "Archetype Breakdown: " . json_encode($archetypeScores) . "\n" .
            "Placement Readiness: {$readiness->placement_readiness_score}%\n" .
            "Interview Readiness: {$readiness->interview_readiness_score}%\n" .
            "Verified Skills: " . json_encode(array_slice($skillProfile, 0, 6));

        $response = Http::withToken($apiKey)
            ->timeout($timeout)
            ->retry(2, 100, throw: false)
            ->post($endpoint, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object'],
            ]);

        if (!$response->successful()) {
            return null;
        }

        $body = $response->json();
        $content = $body['choices'][0]['message']['content'] ?? null;
        if (empty($content)) {
            return null;
        }

        $clean = trim($content);
        if (str_starts_with($clean, '```')) {
            $clean = preg_replace('/^```(?:json)?/i', '', $clean);
            $clean = preg_replace('/```$/', '', $clean);
            $clean = trim($clean);
        }

        $decoded = json_decode($clean, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Validate the structure of the narrative payload.
     */
    protected function validateNarrativePayload(array $payload): bool
    {
        return !empty($payload['executive_summary'])
            && !empty($payload['archetype_analysis'])
            && isset($payload['strengths']) && is_array($payload['strengths'])
            && isset($payload['growth_areas']) && is_array($payload['growth_areas'])
            && isset($payload['roadmap_90_days']) && is_array($payload['roadmap_90_days'])
            && !empty($payload['hiring_pitch']);
    }

    /**
     * Robust deterministic narrative fallback when Groq is unreachable.
     */
    public function deterministicNarrativeFallback(
        User $candidate,
        string $primaryArchetype,
        string $secondaryArchetype,
        array $archetypeScores,
        float $employabilityScore,
        $readiness,
        array $skillProfile
    ): array {
        $primaryMeta = self::ARCHETYPES[$primaryArchetype] ?? self::ARCHETYPES['Builder'];
        $secondaryMeta = self::ARCHETYPES[$secondaryArchetype] ?? self::ARCHETYPES['Analyst'];

        $topSkills = array_slice(array_column($skillProfile, 'skill'), 0, 3);
        $skillsStr = !empty($topSkills) ? implode(', ', $topSkills) : 'PHP, Laravel, and REST APIs';

        $executiveSummary = "Candidate {$candidate->name} exhibits an explainable Employability Score of {$employabilityScore}%, identifying strongly as a {$primaryArchetype} ({$primaryMeta['tagline']}). With an interview readiness rating of {$readiness->interview_readiness_score}% and robust practical capability across {$skillsStr}, the candidate shows high aptitude for junior-to-mid engineering roles.";

        $archetypeAnalysis = "The candidate balances primary mastery as a {$primaryArchetype} ({$archetypeScores[$primaryArchetype]}%) with strong complementary instincts as a {$secondaryArchetype} ({$archetypeScores[$secondaryArchetype]}%). This synthesis enables the candidate to both construct maintainable architectures and methodically evaluate system constraints.";

        $strengths = [
            "Demonstrated engineering craftsmanship with {$primaryArchetype} score of {$archetypeScores[$primaryArchetype]}%.",
            "Consistent curriculum rigor with placement readiness benchmarked at {$readiness->placement_readiness_score}%.",
            "Practical fluency in modern web development including {$skillsStr}.",
        ];

        $growthAreas = [
            "Deepen architectural defenses during technical viva assessments.",
            "Expand automated test coverage and PSR-12 code review compliance.",
            "Conduct structured live code walkthroughs to maximize demo clarity.",
        ];

        $roadmap = [
            [
                'phase' => 'Month 1 (Days 1-30)',
                'timeframe' => 'Days 1-30',
                'objective' => 'Deepen Core Backend Architecture & Code Review Conformance',
                'key_actions' => [
                    'Implement strict PSR-12 compliance and static analysis checks.',
                    'Build advanced Eloquent relationships with query optimization and indexing.',
                ],
            ],
            [
                'phase' => 'Month 2 (Days 31-60)',
                'timeframe' => 'Days 31-60',
                'objective' => 'System Design Defensibility & Microservices Integration',
                'key_actions' => [
                    'Design and defend a fault-tolerant REST API architecture under viva simulation.',
                    'Integrate vector search workflows and asynchronous queue workers.',
                ],
            ],
            [
                'phase' => 'Month 3 (Days 61-90)',
                'timeframe' => 'Days 61-90',
                'objective' => 'Enterprise Placement Readiness & Capstone Delivery',
                'key_actions' => [
                    'Deploy end-to-end containerized application with CI/CD GitHub Actions.',
                    'Complete 3 mock technical interviews targeting primary archetype competencies.',
                ],
            ],
        ];

        $hiringPitch = "{$candidate->name} is a high-potential {$primaryArchetype} with proven competencies across {$skillsStr}, ready to drive immediate value in backend and full-stack engineering squads.";

        return [
            'executive_summary' => $executiveSummary,
            'archetype_analysis' => $archetypeAnalysis,
            'strengths' => $strengths,
            'growth_areas' => $growthAreas,
            'roadmap_90_days' => $roadmap,
            'hiring_pitch' => $hiringPitch,
            'engine' => 'deterministic_heuristic_engine',
        ];
    }

    /**
     * Generate PDF using Laravel DomPDF and store on disk.
     *
     * @param CandidateReport $report
     * @param User $candidate
     * @param array $breakdown
     * @return string Relative storage path
     */
    public function generateAndStorePdf(CandidateReport $report, User $candidate, array $breakdown): string
    {
        $pdf = Pdf::loadView('career.report_pdf', [
            'report' => $report,
            'candidate' => $candidate,
            'breakdown' => $breakdown,
            'archetypes' => self::ARCHETYPES,
            'generatedDate' => now()->format('F j, Y - H:i T'),
        ])->setPaper('a4', 'portrait');

        $relativePath = "reports/candidate_{$candidate->id}_report_{$report->id}.pdf";
        Storage::disk('local')->put($relativePath, $pdf->output());

        return $relativePath;
    }

    /**
     * Return binary PDF stream for a candidate report.
     *
     * @param CandidateReport $report
     * @return \Illuminate\Http\Response
     */
    public function downloadPdf(CandidateReport $report)
    {
        $candidate = $report->user;
        $breakdown = $report->breakdown ?? [];

        $pdf = Pdf::loadView('career.report_pdf', [
            'report' => $report,
            'candidate' => $candidate,
            'breakdown' => $breakdown,
            'archetypes' => self::ARCHETYPES,
            'generatedDate' => $report->created_at->format('F j, Y - H:i T'),
        ])->setPaper('a4', 'portrait');

        $fileName = 'CIA_Career_Report_' . str_replace(' ', '_', $candidate->name) . '_' . $report->id . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * Clamp score strictly between 0.0 and 100.0.
     */
    public function clampScore(float $value): float
    {
        return (float) min(100.0, max(0.0, round($value, 1)));
    }
}
