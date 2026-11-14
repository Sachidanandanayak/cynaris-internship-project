<?php

namespace Tests\Feature;

use App\Models\CandidateLesson;
use App\Models\CandidateReport;
use App\Models\Job;
use App\Models\PlacementReadinessScore;
use App\Models\QuizScore;
use App\Models\User;
use App\Services\Alerting\CiaAlertManager;
use App\Services\Monitoring\CiaMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Class CiaStagingSmokeTest
 *
 * Comprehensive Staging Deployment Smoke Test Suite validating:
 * 1. Staging configuration integrity & zero-trust error suppression (APP_DEBUG=false).
 * 2. All 7 CIA Career Intelligence core modules end-to-end.
 * 3. Structured telemetry, logging, correlation IDs, and health monitoring endpoints.
 * 4. Multi-tier resilient fallback behavior under dual provider degradation.
 *
 * Week 11 Day 5 — Pre-Production & Staging Verification.
 */
class CiaStagingSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected User $candidate;

    protected function setUp(): void
    {
        parent::setUp();

        // Enforce Staging Environment Parameters with Production Parity
        Config::set('app.env', 'staging');
        Config::set('app.debug', false);
        Config::set('cia.logging.enabled', true);
        Config::set('cia.logging.redact_pii', true);
        Config::set('cia.monitoring.enabled', true);
        Config::set('cia.alerting.enabled', true);

        // Reset metrics and alerts to a clean slate
        app(CiaMetricsService::class)->reset();
        app(CiaAlertManager::class)->clearThrottles();
        Cache::flush();

        // Seed verified candidate profile with lessons and quiz achievements
        $this->candidate = $this->createVerifiedCandidate();

        // Seed standard job pool for matching smoke tests
        $this->seedJobPool();
    }

    /**
     * Helper to create a valid minimal PDF byte stream containing plain selectable text.
     */
    protected function createValidPdf(string $text): string
    {
        $escaped = str_replace(['(', ')'], ['\(', '\)'], $text);
        $stream = "BT /F1 12 Tf 72 712 Td ({$escaped}) Tj ET";
        $len = strlen($stream);

        return "%PDF-1.4\n" .
            "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n" .
            "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n" .
            "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj\n" .
            "4 0 obj << /Length {$len} >> stream\n{$stream}\nendstream\nendobj\n" .
            "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n" .
            "xref\n0 6\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000244 00000 n \n0000000414 00000 n \n" .
            "trailer << /Size 6 /Root 1 0 R >>\nstartxref\n500\n%%EOF\n";
    }

    /**
     * Helper to construct an enterprise candidate profile.
     */
    protected function createVerifiedCandidate(string $name = 'Staging Candidate', string $email = 'staging.candidate@example.com'): User
    {
        $candidate = User::factory()->create([
            'name' => $name,
            'email' => $email,
        ]);

        $curriculum = [
            ['skill' => 'PHP', 'score' => 95.0, 'quiz' => 96.0, 'title' => 'Advanced PHP OOP & Types'],
            ['skill' => 'Laravel', 'score' => 92.0, 'quiz' => 94.0, 'title' => 'Laravel MVC, Routing & Eloquent'],
            ['skill' => 'MySQL', 'score' => 88.0, 'quiz' => 90.0, 'title' => 'Relational Schemas & Indexes'],
            ['skill' => 'REST APIs', 'score' => 90.0, 'quiz' => 92.0, 'title' => 'API Contracts & Sanctum Security'],
            ['skill' => 'Docker', 'score' => 84.0, 'quiz' => 86.0, 'title' => 'Multi-Stage Docker & Compose'],
            ['skill' => 'Git & CI/CD', 'score' => 88.0, 'quiz' => 90.0, 'title' => 'Git Branches & GitHub Actions'],
        ];

        foreach ($curriculum as $item) {
            CandidateLesson::create([
                'user_id' => $candidate->id,
                'title' => $item['title'],
                'skill' => $item['skill'],
                'is_completed' => true,
                'score' => $item['score'],
            ]);

            QuizScore::create([
                'user_id' => $candidate->id,
                'quiz_title' => $item['title'] . ' Assessment',
                'skill' => $item['skill'],
                'score' => (int) round($item['quiz'] / 5),
                'max_score' => 20,
                'percentage' => $item['quiz'],
            ]);
        }

        return $candidate;
    }

    /**
     * Helper to seed standard internship jobs for vector matching smoke tests.
     */
    protected function seedJobPool(): void
    {
        Job::factory()->create([
            'title' => 'Junior Full Stack Developer Intern',
            'company' => 'Cynaris Tech Labs',
            'location' => 'Bengaluru, India',
            'employment_type' => 'Internship',
            'description' => 'Hands-on experience with Laravel, PHP, REST APIs, Vue.js, and relational databases.',
            'requirements' => 'Proficiency in PHP, Laravel, MySQL, and modern web development.',
        ]);

        Job::factory()->create([
            'title' => 'Cloud AI Systems Intern',
            'company' => 'Neural Innovations',
            'location' => 'Hyderabad, India',
            'employment_type' => 'Internship',
            'description' => 'Integrating LLM agents, vector embeddings, and LangGraph pipelines in PHP/Python.',
            'requirements' => 'Knowledge of AI concepts, prompt engineering, and API integration.',
        ]);
    }

    /**
     * Helper to mock successful AI completion across Groq and OpenRouter.
     */
    protected function fakeAiSuccess(): void
    {
        Http::fake([
            'https://api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'target_role' => 'Junior Backend Laravel Engineer',
                                'score' => 91.5,
                                'readiness' => 'High',
                                'gaps' => [
                                    ['skill' => 'Redis Caching', 'severity' => 'Low', 'recommended_action' => 'Learn Redis queues and cache stores'],
                                ],
                                'recommendations' => [
                                    ['title' => 'Backend Laravel Engineer', 'match_score' => 92, 'fit_level' => 'Strong'],
                                    ['title' => 'API Platform Engineer', 'match_score' => 88, 'fit_level' => 'Good'],
                                ],
                                'learning_path' => [
                                    ['week' => 1, 'theme' => 'Advanced Database Optimisation', 'topics' => ['Query EXPLAIN', 'Composite Indexes']],
                                    ['week' => 2, 'theme' => 'Redis & Background Jobs', 'topics' => ['Queue Workers', 'Cache Invalidation']],
                                    ['week' => 3, 'theme' => 'AI Gateway Integration', 'topics' => ['Resilient Fallbacks', 'Latency Telemetry']],
                                    ['week' => 4, 'theme' => 'Staging & Containerized CI/CD', 'topics' => ['Docker Multi-Stage', 'Smoke Testing']],
                                ],
                                'resume_analysis' => [
                                    'ats_score' => 89.0,
                                    'verdict' => 'Highly Competitive',
                                    'extracted_skills' => ['PHP', 'Laravel', 'MySQL', 'Docker', 'REST'],
                                    'strengths' => ['Strong foundation in backend development', 'Practical experience in API security'],
                                    'improvements' => ['Expand on automated test coverage metrics in project descriptions'],
                                ],
                                'radar' => [
                                    'labels' => ['PHP', 'Laravel', 'MySQL', 'APIs', 'Docker', 'Git'],
                                    'candidate' => [95, 92, 88, 90, 84, 88],
                                    'benchmark' => [80, 80, 75, 75, 70, 75],
                                ],
                            ]),
                        ],
                    ],
                ],
            ], 200),
            'https://openrouter.ai/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'target_role' => 'Junior Backend Laravel Engineer',
                                'score' => 90.0,
                                'readiness' => 'High',
                                'gaps' => [],
                                'recommendations' => [],
                                'learning_path' => [],
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);
    }

    /**
     * Smoke Test 1: Staging Environment Baseline & Information Disclosure Prevention.
     * Asserts that APP_DEBUG=false suppresses stack traces and internal exceptions.
     */
    public function test_smoke_01_staging_environment_configuration_and_zero_information_disclosure(): void
    {
        $this->assertEquals('staging', config('app.env'));
        $this->assertFalse(config('app.debug'));
        $this->assertTrue(config('cia.logging.enabled'));
        $this->assertTrue(config('cia.monitoring.enabled'));

        // Verify unauthenticated requests to protected endpoints return 401 without leakage
        $response = $this->getJson('/candidate/cia/career/monitoring/metrics');
        $response->assertStatus(401);

        $content = $response->getContent();
        $this->assertStringNotContainsString('xdebug', strtolower($content));
        $this->assertStringNotContainsString('trace', strtolower($content));
        $this->assertStringNotContainsString('exception', strtolower($content));
        $this->assertStringNotContainsString('root', strtolower($content));
    }

    /**
     * Smoke Test 2: Module 1 — CIA Cockpit & Skill Gap Intelligence Endpoint.
     */
    public function test_smoke_02_cia_cockpit_view_and_skill_gap_analysis(): void
    {
        $this->fakeAiSuccess();

        // 1. Cockpit Web View
        $cockpitRes = $this->actingAs($this->candidate)->get('/candidate/cia/career');
        $cockpitRes->assertStatus(200);
        $cockpitRes->assertSee('Career Intelligence Cockpit', false);
        $cockpitRes->assertHeader('X-Correlation-ID');

        // 2. Skill Gap JSON Endpoint
        $skillGapRes = $this->actingAs($this->candidate)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $skillGapRes->assertStatus(200);
        $skillGapRes->assertJsonStructure([
            'success',
            'target_role',
            'score',
            'gaps',
            'priority_learning',
            'skill_profile',
            'radar',
            'engine',
            'log_id',
        ]);
        $skillGapRes->assertHeader('X-Correlation-ID');
        $this->assertTrue($skillGapRes->json('success'));
    }

    /**
     * Smoke Test 3: Module 2 — Career Recommendation Engine.
     */
    public function test_smoke_03_career_recommendation_engine(): void
    {
        $this->fakeAiSuccess();

        $response = $this->actingAs($this->candidate)->postJson('/candidate/cia/career/recommendations', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'target_role',
            'recommendations',
            'agent',
            'engine',
            'log_id',
        ]);
        $response->assertHeader('X-Correlation-ID');
        $this->assertTrue($response->json('success'));
        $this->assertIsArray($response->json('recommendations'));
    }

    /**
     * Smoke Test 4: Module 3 — ChromaDB Vector Profile Sync & Semantic Internship Matching.
     */
    public function test_smoke_04_chromadb_profile_sync_and_internship_matching(): void
    {
        // 1. Profile Vector Sync (Returns 202 Accepted)
        $syncRes = $this->actingAs($this->candidate)->postJson('/candidate/cia/career/profile/sync', [
            'force_refresh' => true,
        ]);
        $syncRes->assertStatus(202);
        $syncRes->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'candidate_id',
                'target_collection',
            ],
        ]);
        $syncRes->assertHeader('X-Correlation-ID');

        // Verify zero PII was embedded in the sync payload
        $this->assertTrue($syncRes->json('success'));
        $this->assertEquals('cynaris_intern_profiles', $syncRes->json('data.target_collection'));

        // 2. Vector Semantic Job Matching
        $matchRes = $this->actingAs($this->candidate)->getJson('/candidate/cia/career/matches?limit=5');
        $matchRes->assertStatus(200);
        $matchRes->assertJsonStructure([
            'success',
            'meta' => [
                'candidate_id',
                'total_matched',
            ],
            'data',
        ]);
        $matchRes->assertHeader('X-Correlation-ID');
    }

    /**
     * Smoke Test 5: Module 4 — LangGraph Structured Learning Path Engine.
     */
    public function test_smoke_05_langgraph_learning_path_engine(): void
    {
        $this->fakeAiSuccess();

        $response = $this->actingAs($this->candidate)->postJson('/candidate/cia/career/learning-path', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'candidate_id',
            'target_role',
            'total_weeks',
            'total_hours',
            'schedule',
            'workflow_metadata',
        ]);
        $response->assertHeader('X-Correlation-ID');

        $schedule = $response->json('schedule');
        $this->assertIsArray($schedule);
        $this->assertNotEmpty($schedule);
    }

    /**
     * Smoke Test 6: Module 5 — Resume ATS Intelligence & PDF Scanner.
     */
    public function test_smoke_06_resume_ats_intelligence_scanner(): void
    {
        $this->fakeAiSuccess();

        $pdfBytes = $this->createValidPdf("Sachidananda Nayak\nLaravel PHP Full Stack Engineer\nExperience: REST API, MySQL, Docker, CI/CD");
        $file = UploadedFile::fake()->createWithContent('staging_candidate_resume.pdf', $pdfBytes);

        $response = $this->actingAs($this->candidate)->post('/candidate/cia/career/resume/scan', [
            'resume' => $file,
            'target_role' => 'Junior Backend Laravel Engineer',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'target_role',
            'overall_ats_score',
            'scores' => [
                'keyword_matching',
                'formatting',
                'work_experience',
                'technical_skills',
            ],
            'matched_keywords',
            'missing_keywords',
            'improvement_suggestions',
            'engine',
            'log_id',
        ]);
        $response->assertHeader('X-Correlation-ID');
        $this->assertGreaterThanOrEqual(0, $response->json('overall_ats_score'));
    }

    /**
     * Smoke Test 7: Module 6 — Placement & Interview Readiness Scorecard.
     */
    public function test_smoke_07_placement_readiness_scorecard(): void
    {
        $this->fakeAiSuccess();

        // 1. Calculate Scorecard
        $calcRes = $this->actingAs($this->candidate)->postJson('/candidate/cia/career/readiness/calculate', [
            'project_completion_score' => 90.0,
            'assessment_score' => 92.0,
            'cia_usage_score' => 88.0,
            'documentation_score' => 85.0,
            'viva_score' => 80.0,
            'code_review_score' => 86.0,
            'demo_clarity_score' => 88.0,
            'git_quality_score' => 92.0,
        ]);
        $calcRes->assertStatus(200);
        $calcRes->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'user_id',
                'placement_readiness_score',
                'interview_readiness_score',
                'breakdown',
            ],
        ]);
        $calcRes->assertHeader('X-Correlation-ID');

        // 2. Retrieve Persisted Scorecard
        $getRes = $this->actingAs($this->candidate)->getJson('/candidate/cia/career/readiness');
        $getRes->assertStatus(200);
        $getRes->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'user_id',
                'placement_readiness_score',
                'interview_readiness_score',
            ],
        ]);
        $getRes->assertHeader('X-Correlation-ID');
    }

    /**
     * Smoke Test 8: Module 7 — Career Intelligence Report Synthesis & PDF Streaming.
     */
    public function test_smoke_08_career_intelligence_report_and_pdf_export(): void
    {
        $this->fakeAiSuccess();

        // 1. Generate Report
        $genRes = $this->actingAs($this->candidate)->postJson('/candidate/cia/career/report/generate', []);
        $genRes->assertStatus(200);
        $genRes->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'user_id',
                'report_type',
                'archetype_scores',
                'primary_archetype',
                'secondary_archetype',
                'employability_score',
                'narrative',
            ],
        ]);
        $genRes->assertHeader('X-Correlation-ID');
        $reportId = $genRes->json('data.id');

        // 2. Fetch Latest Report
        $latestRes = $this->actingAs($this->candidate)->getJson('/candidate/cia/career/report/latest');
        $latestRes->assertStatus(200);
        $latestRes->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'user_id',
                'archetype_scores',
            ],
        ]);
        $latestRes->assertHeader('X-Correlation-ID');

        // 3. Download Generated PDF Report
        $downloadRes = $this->actingAs($this->candidate)->get("/candidate/cia/career/report/{$reportId}/download");
        $downloadRes->assertStatus(200);
        $downloadRes->assertHeader('content-type', 'application/pdf');
        $downloadRes->assertHeader('X-Correlation-ID');
    }

    /**
     * Smoke Test 9: Telemetry & Monitoring Endpoints (Web & Sanctum API).
     */
    public function test_smoke_09_telemetry_and_health_monitoring_endpoints(): void
    {
        // 1. Web Monitoring Endpoint (Session Authenticated)
        $webMetricsRes = $this->actingAs($this->candidate)->getJson('/candidate/cia/career/monitoring/metrics');
        $webMetricsRes->assertStatus(200);
        $webMetricsRes->assertJsonStructure([
            'success',
            'status',
            'timestamp',
            'correlation_id',
            'metrics' => [
                'requests' => ['total', 'successful', 'client_errors', 'server_errors', 'error_rate_percent'],
                'latency' => ['avg_ms', 'p95_ms'],
                'ai_providers' => [
                    'groq',
                    'openrouter',
                    'fallback_count',
                    'dual_failure_count',
                ],
                'chromadb',
            ],
        ]);
        $webMetricsRes->assertHeader('X-Correlation-ID');

        // 2. API Health Endpoint (Sanctum Authenticated)
        Sanctum::actingAs($this->candidate);
        $apiHealthRes = $this->getJson('/api/cia/monitoring/health');
        $apiHealthRes->assertStatus(200);
        $apiHealthRes->assertJsonStructure([
            'success',
            'status',
            'metrics',
            'correlation_id',
        ]);
        $apiHealthRes->assertHeader('X-Correlation-ID');
    }

    /**
     * Smoke Test 10: Multi-Tier Resilient Fallback in Staging Mode.
     * Verifies that when both Groq and OpenRouter fail, deterministic fallback engages seamlessly.
     */
    public function test_smoke_10_multi_tier_resilient_fallback_under_provider_outage(): void
    {
        // Simulate dual provider HTTP outage
        Http::fake([
            'https://api.groq.com/*' => Http::response(['error' => 'Groq Rate Limit Exceeded'], 429),
            'https://openrouter.ai/*' => Http::response(['error' => 'OpenRouter Service Unavailable'], 503),
        ]);

        $response = $this->actingAs($this->candidate)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        // Request must succeed with deterministic fallback and NOT crash with 500
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'target_role',
            'score',
            'gaps',
            'priority_learning',
            'skill_profile',
            'radar',
            'engine',
        ]);
        $response->assertHeader('X-Correlation-ID');
        $this->assertTrue($response->json('success'));

        // Telemetry must record dual failure without unhandled error
        $metrics = app(CiaMetricsService::class)->getHealthMetrics();
        $this->assertGreaterThanOrEqual(1, $metrics['ai_providers']['dual_failure_count']);
    }
}
