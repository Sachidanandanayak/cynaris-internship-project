<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class CareerRecommendationService
{
    /**
     * Generate 3 career path recommendations using CrewAI Agent.
     *
     * @param User $candidate
     * @param array $skillProfile
     * @param array $gaps
     * @param string $targetRole
     * @return array{agent: string, target_role: string, recommendations_count: int, paths: array, engine: string}
     */
    public function generateRecommendations(User $candidate, array $skillProfile, array $gaps, string $targetRole): array
    {
        $targetRole = trim($targetRole) ?: 'Junior Backend Laravel Engineer';

        $payload = [
            'target_role' => $targetRole,
            'candidate_id' => $candidate->id,
            'candidate_name' => $candidate->name,
            'skill_profile' => $skillProfile,
            'gaps' => $gaps,
        ];

        // Attempt Python CrewAI agent execution
        try {
            $pythonResult = $this->invokePythonAgent($payload);
            if ($pythonResult !== null && $this->validatePaths($pythonResult['paths'] ?? null)) {
                return [
                    'agent' => $pythonResult['agent'] ?? 'CareerAdvisorAgent',
                    'target_role' => $targetRole,
                    'recommendations_count' => count($pythonResult['paths']),
                    'paths' => $pythonResult['paths'],
                    'engine' => 'crewai',
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('CrewAI Python agent execution failed, engaging fallback: ' . $e->getMessage(), [
                'user_id' => $candidate->id,
                'target_role' => $targetRole,
            ]);
        }

        // Fallback: Resilient synthesized recommendations
        $fallbackPaths = $this->fallbackRecommendations($targetRole, $skillProfile, $gaps);

        return [
            'agent' => 'CareerAdvisorAgent',
            'target_role' => $targetRole,
            'recommendations_count' => count($fallbackPaths),
            'paths' => $fallbackPaths,
            'engine' => 'crewai_fallback',
        ];
    }

    /**
     * Invoke the Python microservice / script.
     */
    public function invokePythonAgent(array $payload): ?array
    {
        $possiblePaths = [
            base_path('scripts/crewai_career_advisor.py'),
            base_path('../scripts/crewai_career_advisor.py'),
            app_path('../scripts/crewai_career_advisor.py'),
        ];

        $scriptPath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $scriptPath = realpath($path);
                break;
            }
        }

        if (!$scriptPath) {
            Log::warning('crewai_career_advisor.py not found in configured paths.');
            return null;
        }

        $inputJson = json_encode($payload);
        $tempInput = tempnam(sys_get_temp_dir(), 'cia_crew_');
        if ($tempInput === false) {
            return null;
        }

        file_put_contents($tempInput, $inputJson);

        $apiKey = config('services.groq.key') ?: '';
        $cmd = 'python ' . escapeshellarg($scriptPath) . ' ' . escapeshellarg($tempInput);

        if (!empty($apiKey)) {
            putenv("GROQ_API_KEY={$apiKey}");
        }

        $stdout = shell_exec($cmd);

        @unlink($tempInput);

        if (empty($stdout)) {
            Log::warning('Empty response from CrewAI Python advisor.');
            return null;
        }

        // Parse JSON output from stdout
        $trimmed = trim($stdout);
        $start = strpos($trimmed, '{');
        $end = strrpos($trimmed, '}');

        if ($start !== false && $end !== false) {
            $jsonStr = substr($trimmed, $start, $end - $start + 1);
            $parsed = json_decode($jsonStr, true);
            if (is_array($parsed) && isset($parsed['paths']) && is_array($parsed['paths'])) {
                return $parsed;
            }
        }

        Log::warning('Failed to parse valid JSON from Python agent output: ' . substr($trimmed, 0, 300));
        return null;
    }

    /**
     * Validate that the paths array contains exactly 3 (or at least 3) properly structured career recommendations.
     */
    protected function validatePaths(?array $paths): bool
    {
        if (!is_array($paths) || count($paths) < 3) {
            return false;
        }

        foreach (array_slice($paths, 0, 3) as $path) {
            if (!is_array($path)) {
                return false;
            }
            if (empty($path['title']) || !isset($path['fit_percentage']) || empty($path['rationale']) || empty($path['roadmap'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Resilient fallback recommendations synthesizer.
     */
    public function fallbackRecommendations(string $targetRole, array $skillProfile, array $gaps): array
    {
        $roleLower = strtolower($targetRole);

        $strongSkills = array_filter($skillProfile, fn ($s) => ($s['proficiency_score'] ?? 0) >= 75);
        $strongNames = array_column($strongSkills, 'skill');
        $strongStr = !empty($strongNames) ? implode(', ', $strongNames) : 'Core backend fundamentals';

        $highGaps = array_filter($gaps, fn ($g) => ($g['impact'] ?? '') === 'High');
        $gapNames = array_column($highGaps ?: $gaps, 'skill');
        $gapStr = !empty($gapNames) ? implode(', ', array_slice($gapNames, 0, 2)) : 'production containerization';

        if (str_contains($roleLower, 'back') || str_contains($roleLower, 'php') || str_contains($roleLower, 'laravel')) {
            return [
                [
                    'title' => 'Backend Laravel & API Microservices Engineer',
                    'fit_percentage' => 92,
                    'rationale' => "Your strong verified proficiencies in {$strongStr} position you as an immediate contender for high-velocity backend engineering roles. Closing the deficit in {$gapStr} will solidify your enterprise readiness.",
                    'roadmap' => 'Step 1: Containerize Laravel apps with Docker Compose. Step 2: Implement Redis caching and job queues. Step 3: Architect microservices with Sanctum token security.',
                ],
                [
                    'title' => 'Full-Stack Enterprise Web Application Developer',
                    'fit_percentage' => 85,
                    'rationale' => 'Leverage your proven backend framework mastery while expanding your frontend async capabilities to build complete end-to-end recruitment and enterprise platforms.',
                    'roadmap' => 'Step 1: Integrate Vue.js 3 / React SPA frontends with Laravel RESTful APIs. Step 2: Master Tailwind CSS component architecture. Step 3: Implement real-time WebSockets.',
                ],
                [
                    'title' => 'DevOps & Cloud Systems Automation Intern',
                    'fit_percentage' => 76,
                    'rationale' => "Builds directly on your automated testing and Git CI/CD workflow habits by tackling {$gapStr} to transition into cloud platform engineering.",
                    'roadmap' => 'Step 1: Master multi-stage Docker builds. Step 2: Construct GitHub Actions automated test & deploy pipelines. Step 3: Deploy resilient apps on Linux PaaS/AWS.',
                ],
            ];
        }

        if (str_contains($roleLower, 'front') || str_contains($roleLower, 'ui')) {
            return [
                [
                    'title' => 'Frontend UI/UX Systems Specialist',
                    'fit_percentage' => 90,
                    'rationale' => 'Capitalizes on your frontend and styling strengths, turning interface craft into high-demand product engineering capabilities.',
                    'roadmap' => 'Step 1: Master component state architectures. Step 2: Integrate Chart.js and reactive data telemetry. Step 3: Implement zero-trust frontend auth flows.',
                ],
                [
                    'title' => 'Full-Stack JavaScript & PHP Engineer',
                    'fit_percentage' => 84,
                    'rationale' => "Bridges your interface expertise with verified {$strongStr} to unlock high-yield full-stack responsibilities.",
                    'roadmap' => 'Step 1: Connect modern SPA frontends to Laravel REST endpoints. Step 2: Implement optimistic UI updates. Step 3: Build automated Cypress E2E test suites.',
                ],
                [
                    'title' => 'Design Systems & Web Performance Engineer',
                    'fit_percentage' => 78,
                    'rationale' => 'High-impact niche focused on design tokens, accessibility standards (WCAG 2.1), and core web vitals optimization.',
                    'roadmap' => 'Step 1: Build atomic design systems. Step 2: Optimize asset delivery pipelines and tree-shaking. Step 3: Automate Lighthouse CI testing.',
                ],
            ];
        }

        return [
            [
                'title' => "Full-Stack {$targetRole} Specialist",
                'fit_percentage' => 88,
                'rationale' => "Strong demonstrated competence in {$strongStr} aligns directly with current software engineering job market demand.",
                'roadmap' => "Step 1: Target primary competency gaps ({$gapStr}). Step 2: Build full-stack capstone projects with automated CI/CD. Step 3: Deploy live on cloud PaaS.",
            ],
            [
                'title' => 'API & Systems Integration Developer',
                'fit_percentage' => 82,
                'rationale' => 'Focuses on building robust, token-authenticated RESTful services and third-party SaaS integrations.',
                'roadmap' => 'Step 1: Implement OpenAPI/Swagger contract testing. Step 2: Build resilient webhook consumers. Step 3: Implement rate limiting and defensive exception handlers.',
            ],
            [
                'title' => 'Cloud Application & Automation Engineer',
                'fit_percentage' => 75,
                'rationale' => "Accelerates career velocity by systematically closing {$gapStr} to handle enterprise infrastructure and delivery pipelines.",
                'roadmap' => 'Step 1: Master containerization and environment isolation. Step 2: Construct multi-tier CI/CD pipelines. Step 3: Implement automated telemetry and monitoring.',
            ],
        ];
    }
}
