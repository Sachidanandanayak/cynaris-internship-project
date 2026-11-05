<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SkillGapAnalysisService
{
    /**
     * Standard role benchmarks: skill -> minimum target proficiency (0-100)
     */
    protected array $roleBenchmarks = [
        'backend' => [
            'PHP' => 85,
            'Laravel' => 85,
            'MySQL' => 80,
            'REST APIs' => 85,
            'Git & CI/CD' => 75,
            'Docker' => 70,
            'Redis' => 60,
        ],
        'fullstack' => [
            'PHP' => 80,
            'Laravel' => 80,
            'JavaScript' => 85,
            'MySQL' => 75,
            'REST APIs' => 80,
            'Docker' => 65,
            'Git & CI/CD' => 75,
        ],
        'frontend' => [
            'JavaScript' => 90,
            'REST APIs' => 80,
            'Git & CI/CD' => 75,
            'Tailwind CSS' => 85,
            'React/Vue' => 80,
        ],
        'devops' => [
            'Docker' => 85,
            'Git & CI/CD' => 85,
            'Linux' => 80,
            'REST APIs' => 70,
            'Kubernetes' => 70,
        ],
        'default' => [
            'PHP' => 80,
            'Laravel' => 80,
            'MySQL' => 75,
            'REST APIs' => 80,
            'JavaScript' => 75,
            'Git & CI/CD' => 75,
            'Docker' => 65,
        ],
    ];

    /**
     * Analyze candidate skill profile against target role.
     *
     * @param User $candidate
     * @param array $skillProfile
     * @param string $targetRole
     * @return array{target_role: string, score: float, gaps: array, priority_learning: array, benchmark_comparison: array, engine: string}
     */
    public function analyze(User $candidate, array $skillProfile, string $targetRole): array
    {
        $targetRole = trim($targetRole);
        if (empty($targetRole)) {
            $targetRole = 'Junior Backend Laravel Engineer';
        }

        // Map candidate profile to skill-keyed lookup
        $candidateMap = [];
        foreach ($skillProfile as $item) {
            if (isset($item['skill'])) {
                $candidateMap[$item['skill']] = (float) ($item['proficiency_score'] ?? 0.0);
            }
        }

        // Resolve benchmark for target role
        $benchmark = $this->resolveBenchmark($targetRole);

        // Try Groq API first if key configured
        $apiKey = config('services.groq.key');
        if (!empty($apiKey)) {
            try {
                $groqResult = $this->queryGroq($skillProfile, $targetRole, $benchmark);
                if ($groqResult !== null) {
                    $groqResult['benchmark_comparison'] = $this->buildBenchmarkComparison($candidateMap, $benchmark);
                    return $groqResult;
                }
            } catch (\Throwable $e) {
                Log::warning('Groq skill gap query failed, engaging deterministic fallback: ' . $e->getMessage(), [
                    'candidate_id' => $candidate->id,
                    'target_role' => $targetRole,
                ]);
            }
        }

        // Deterministic heuristic fallback
        return $this->deterministicAnalysis($candidateMap, $benchmark, $targetRole);
    }

    /**
     * Resolve closest benchmark profile for target role name.
     */
    public function resolveBenchmark(string $targetRole): array
    {
        $roleLower = strtolower($targetRole);

        if (str_contains($roleLower, 'full') || str_contains($roleLower, 'stack')) {
            return $this->roleBenchmarks['fullstack'];
        }
        if (str_contains($roleLower, 'front') || str_contains($roleLower, 'ui') || str_contains($roleLower, 'web')) {
            return $this->roleBenchmarks['frontend'];
        }
        if (str_contains($roleLower, 'devops') || str_contains($roleLower, 'cloud') || str_contains($roleLower, 'infra')) {
            return $this->roleBenchmarks['devops'];
        }
        if (str_contains($roleLower, 'back') || str_contains($roleLower, 'laravel') || str_contains($roleLower, 'php')) {
            return $this->roleBenchmarks['backend'];
        }

        return $this->roleBenchmarks['default'];
    }

    /**
     * Query Groq AI API for skill gap reasoning.
     */
    protected function queryGroq(array $skillProfile, string $targetRole, array $benchmark): ?array
    {
        $apiKey = config('services.groq.key');
        $model = config('services.groq.model', 'llama-3.3-70b-versatile');
        $endpoint = config('services.groq.endpoint', 'https://api.groq.com/openai/v1/chat/completions');
        $timeout = config('services.groq.timeout', 10);

        $systemPrompt = "You are CIA Career Advisor, an enterprise AI career intelligence coach for Cynaris Solutions interns. " .
            "Analyze the candidate's skill profile against the required competencies for the target role. " .
            "Calculate an overall readiness score (0-100), identify specific skill gaps with severity impact (High/Medium/Low), " .
            "and suggest priority learning modules to close those gaps. " .
            "Return ONLY a valid JSON object matching this schema: " .
            '{"score": number, "gaps": [{"skill": string, "candidate_level": string, "required_level": string, "impact": "High"|"Medium"|"Low", "description": string}], "priority_learning": [{"module": string, "priority": "High"|"Medium"|"Low", "estimated_hours": number, "actionable_steps": string}]}';

        $userPayload = [
            'target_role' => $targetRole,
            'target_benchmarks' => $benchmark,
            'candidate_profile' => $skillProfile,
        ];

        $response = Http::withToken($apiKey)
            ->timeout($timeout)
            ->post($endpoint, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => json_encode($userPayload, JSON_PRETTY_PRINT)],
                ],
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object'],
            ]);

        if (!$response->successful()) {
            Log::warning('Groq API returned HTTP status ' . $response->status(), [
                'body' => $response->body(),
            ]);
            return null;
        }

        $body = $response->json();
        $content = $body['choices'][0]['message']['content'] ?? null;
        if (empty($content)) {
            return null;
        }

        $parsed = json_decode($content, true);
        if (!is_array($parsed) || !isset($parsed['score']) || !isset($parsed['gaps'])) {
            return null;
        }

        // Validate and sanitize parsed output
        $score = max(0.0, min(100.0, (float) $parsed['score']));
        $gaps = [];
        if (is_array($parsed['gaps'])) {
            foreach ($parsed['gaps'] as $gap) {
                if (is_array($gap) && !empty($gap['skill'])) {
                    $gaps[] = [
                        'skill' => (string) $gap['skill'],
                        'candidate_level' => (string) ($gap['candidate_level'] ?? 'Novice'),
                        'required_level' => (string) ($gap['required_level'] ?? 'Intermediate'),
                        'impact' => in_array($gap['impact'] ?? '', ['High', 'Medium', 'Low']) ? $gap['impact'] : 'Medium',
                        'description' => (string) ($gap['description'] ?? "Deficit identified in {$gap['skill']}."),
                    ];
                }
            }
        }

        $priorityLearning = [];
        if (is_array($parsed['priority_learning'] ?? null)) {
            foreach ($parsed['priority_learning'] as $item) {
                if (is_array($item) && !empty($item['module'])) {
                    $priorityLearning[] = [
                        'module' => (string) $item['module'],
                        'priority' => in_array($item['priority'] ?? '', ['High', 'Medium', 'Low']) ? $item['priority'] : 'Medium',
                        'estimated_hours' => (float) ($item['estimated_hours'] ?? 4.0),
                        'actionable_steps' => (string) ($item['actionable_steps'] ?? 'Review curriculum modules and complete exercises.'),
                    ];
                }
            }
        }

        return [
            'target_role' => $targetRole,
            'score' => round($score, 1),
            'gaps' => $gaps,
            'priority_learning' => $priorityLearning,
            'engine' => 'groq_ai',
        ];
    }

    /**
     * Deterministic algorithmic gap analysis engine.
     */
    public function deterministicAnalysis(array $candidateMap, array $benchmark, string $targetRole): array
    {
        $gaps = [];
        $priorityLearning = [];
        $totalWeight = 0;
        $earnedScore = 0;

        foreach ($benchmark as $skill => $requiredScore) {
            $totalWeight += $requiredScore;
            $candScore = $candidateMap[$skill] ?? 0.0;
            $earnedScore += min($requiredScore, $candScore);

            $delta = $requiredScore - $candScore;
            if ($delta > 10.0) {
                $impact = match (true) {
                    $delta >= 30.0 => 'High',
                    $delta >= 20.0 => 'Medium',
                    default => 'Low',
                };

                $candLevel = match (true) {
                    $candScore >= 80.0 => 'Advanced',
                    $candScore >= 60.0 => 'Intermediate',
                    $candScore > 0.0 => 'Beginner',
                    default => 'None',
                };

                $reqLevel = match (true) {
                    $requiredScore >= 80 => 'Advanced',
                    $requiredScore >= 65 => 'Intermediate',
                    default => 'Foundational',
                };

                $gaps[] = [
                    'skill' => $skill,
                    'candidate_level' => $candLevel,
                    'required_level' => $reqLevel,
                    'impact' => $impact,
                    'deficit_points' => round($delta, 1),
                    'description' => "Candidate proficiency ({$candScore}%) is below the {$requiredScore}% benchmark for {$skill}.",
                ];

                $priorityLearning[] = [
                    'module' => "Mastering {$skill} for {$targetRole}",
                    'priority' => $impact,
                    'estimated_hours' => round(max(3.0, $delta / 5.0), 1),
                    'actionable_steps' => "Complete hands-on assessment modules and coding challenges for {$skill}.",
                ];
            }
        }

        // Sort gaps by deficit descending
        usort($gaps, fn ($a, $b) => ($b['deficit_points'] ?? 0) <=> ($a['deficit_points'] ?? 0));
        usort($priorityLearning, function ($a, $b) {
            $rank = ['High' => 3, 'Medium' => 2, 'Low' => 1];
            return ($rank[$b['priority']] ?? 0) <=> ($rank[$a['priority']] ?? 0);
        });

        $readinessScore = $totalWeight > 0 ? round(($earnedScore / $totalWeight) * 100.0, 1) : 0.0;

        return [
            'target_role' => $targetRole,
            'score' => $readinessScore,
            'gaps' => $gaps,
            'priority_learning' => $priorityLearning,
            'benchmark_comparison' => $this->buildBenchmarkComparison($candidateMap, $benchmark),
            'engine' => 'deterministic_pipeline',
        ];
    }

    /**
     * Build radar chart comparison payload between candidate skills and benchmark.
     */
    public function buildBenchmarkComparison(array $candidateMap, array $benchmark): array
    {
        $labels = array_keys($benchmark);
        $candidateData = [];
        $requiredData = [];

        foreach ($labels as $skill) {
            $candidateData[] = round($candidateMap[$skill] ?? 0.0, 1);
            $requiredData[] = round((float) $benchmark[$skill], 1);
        }

        return [
            'labels' => $labels,
            'candidate_scores' => $candidateData,
            'required_scores' => $requiredData,
        ];
    }
}
