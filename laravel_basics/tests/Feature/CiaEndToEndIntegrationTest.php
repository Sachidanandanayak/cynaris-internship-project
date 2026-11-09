<?php

namespace Tests\Feature;

use App\Models\CandidateLesson;
use App\Models\CandidateReport;
use App\Models\CiaSearchLog;
use App\Models\Job;
use App\Models\PlacementReadinessScore;
use App\Models\QuizScore;
use App\Models\User;
use App\Services\Vector\ChromaDbService;
use App\Services\Vector\CiaMemoryLayerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CiaEndToEndIntegrationTest extends TestCase
{
    use RefreshDatabase;

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
     * Helper to set up an enterprise candidate profile with completed lessons and quiz scores.
     */
    protected function createVerifiedCandidate(string $name = 'Dev Candidate', string $email = 'candidate.e2e@example.com'): User
    {
        $candidate = User::factory()->create([
            'name' => $name,
            'email' => $email,
        ]);

        $curriculum = [
            ['skill' => 'PHP', 'score' => 92.0, 'quiz' => 95.0, 'title' => 'Advanced PHP OOP & Types'],
            ['skill' => 'Laravel', 'score' => 90.0, 'quiz' => 92.0, 'title' => 'Laravel MVC, Routing & Eloquent'],
            ['skill' => 'MySQL', 'score' => 85.0, 'quiz' => 88.0, 'title' => 'Relational Schemas & Indexes'],
            ['skill' => 'REST APIs', 'score' => 88.0, 'quiz' => 90.0, 'title' => 'API Contracts & Sanctum Security'],
            ['skill' => 'Git & CI/CD', 'score' => 82.0, 'quiz' => 85.0, 'title' => 'Git Branches & GitHub Actions'],
            ['skill' => 'Docker', 'score' => 75.0, 'quiz' => 78.0, 'title' => 'Multi-Stage Docker & Compose'],
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
                'quiz_title' => $item['title'] . ' Evaluation',
                'skill' => $item['skill'],
                'score' => (int) round($item['quiz'] / 5),
                'max_score' => 20,
                'percentage' => $item['quiz'],
            ]);
        }

        return $candidate;
    }

    /**
     * Test 1: Unauthenticated requests across all CIA Career endpoints are rejected with 401.
     */
    public function test_unauthenticated_requests_are_rejected_across_all_cia_endpoints(): void
    {
        $endpoints = [
            ['method' => 'postJson', 'uri' => '/candidate/cia/career/skill-gap', 'payload' => ['target_role' => 'Junior Backend Laravel Engineer']],
            ['method' => 'postJson', 'uri' => '/candidate/cia/career/recommendations', 'payload' => ['target_role' => 'Junior Backend Laravel Engineer']],
            ['method' => 'postJson', 'uri' => '/candidate/cia/career/profile/sync', 'payload' => []],
            ['method' => 'getJson',  'uri' => '/candidate/cia/career/matches'],
            ['method' => 'postJson', 'uri' => '/candidate/cia/career/learning-path', 'payload' => ['target_role' => 'Junior Backend Laravel Engineer']],
            ['method' => 'postJson', 'uri' => '/candidate/cia/career/resume/scan', 'payload' => []],
            ['method' => 'postJson', 'uri' => '/candidate/cia/career/readiness/calculate', 'payload' => []],
            ['method' => 'getJson',  'uri' => '/candidate/cia/career/readiness'],
            ['method' => 'postJson', 'uri' => '/candidate/cia/career/report/generate', 'payload' => []],
            ['method' => 'getJson',  'uri' => '/candidate/cia/career/report/latest'],
            ['method' => 'getJson',  'uri' => '/candidate/cia/career/memory/match'],
            ['method' => 'postJson', 'uri' => '/candidate/cia/career/memory/seed', 'payload' => []],
        ];

        foreach ($endpoints as $ep) {
            $method = $ep['method'];
            $uri = $ep['uri'];
            $payload = $ep['payload'] ?? [];

            $response = empty($payload)
                ? $this->$method($uri)
                : $this->$method($uri, $payload);

            $this->assertEquals(
                401,
                $response->status(),
                "Failed asserting that endpoint {$uri} returns HTTP 401 for unauthenticated requests."
            );
        }
    }

    /**
     * Test 2: Full End-to-End Sequential Lifecycle across all seven core modules and W10D4 Memory Layer.
     */
    public function test_sequential_candidate_lifecycle_across_all_seven_modules_and_memory_layer(): void
    {
        // Setup authenticated candidate with complete learning history
        $candidate = $this->createVerifiedCandidate('Arjun Mehta', 'arjun.mehta@example.com');
        $targetRole = 'Junior Backend Laravel Engineer';

        // ---------------------------------------------------------------------
        // Module 1 (W9D2): Skill Gap Analysis
        // ---------------------------------------------------------------------
        $m1Response = $this->actingAs($candidate)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => $targetRole,
        ]);

        $m1Response->assertStatus(200);
        $m1Response->assertJsonStructure([
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
        $m1Data = $m1Response->json();
        $this->assertTrue($m1Data['success']);
        $this->assertEquals($targetRole, $m1Data['target_role']);
        $this->assertGreaterThanOrEqual(0.0, $m1Data['score']);
        $this->assertLessThanOrEqual(100.0, $m1Data['score']);
        $this->assertIsArray($m1Data['gaps']);
        $this->assertIsArray($m1Data['priority_learning']);
        $this->assertDatabaseHas('cia_search_logs', [
            'id' => $m1Data['log_id'],
            'user_id' => $candidate->id,
            'query_type' => 'skill_gap',
        ]);

        // ---------------------------------------------------------------------
        // Module 2 (W9D3): Career Recommendations Engine (CrewAI Agent)
        // ---------------------------------------------------------------------
        $m2Response = $this->actingAs($candidate)->postJson('/candidate/cia/career/recommendations', [
            'target_role' => $targetRole,
        ]);

        $m2Response->assertStatus(200);
        $m2Response->assertJsonStructure([
            'success',
            'target_role',
            'recommendations',
            'agent',
            'engine',
            'log_id',
        ]);
        $m2Data = $m2Response->json();
        $this->assertTrue($m2Data['success']);
        $this->assertCount(3, $m2Data['recommendations'], 'Must recommend exactly three distinct career paths.');
        foreach ($m2Data['recommendations'] as $rec) {
            $this->assertArrayHasKey('title', $rec);
            $this->assertArrayHasKey('fit_percentage', $rec);
            $this->assertArrayHasKey('rationale', $rec);
            $this->assertArrayHasKey('roadmap', $rec);
            $this->assertGreaterThanOrEqual(0, $rec['fit_percentage']);
            $this->assertLessThanOrEqual(100, $rec['fit_percentage']);
        }
        $this->assertDatabaseHas('cia_search_logs', [
            'id' => $m2Data['log_id'],
            'user_id' => $candidate->id,
            'query_type' => 'career_recommendation',
        ]);

        // ---------------------------------------------------------------------
        // Module 3 (W9D4): Vector Internship Matching (cynaris_intern_profiles)
        // ---------------------------------------------------------------------
        // First sync candidate profile into vector store (202 Accepted)
        $syncResponse = $this->actingAs($candidate)->postJson('/candidate/cia/career/profile/sync', [
            'force_refresh' => true,
        ]);
        $syncResponse->assertStatus(202);
        $this->assertTrue($syncResponse->json('success'));
        $this->assertEquals('cynaris_intern_profiles', $syncResponse->json('data.target_collection'));

        // Query internship matches
        $m3Response = $this->actingAs($candidate)->getJson('/candidate/cia/career/matches?limit=5');
        $m3Response->assertStatus(200);
        $m3Response->assertJsonStructure([
            'success',
            'meta' => [
                'candidate_id',
                'total_matched',
                'query_parameters',
                'engine',
            ],
            'data',
        ]);
        $m3Data = $m3Response->json();
        $this->assertTrue($m3Data['success']);
        $this->assertLessThanOrEqual(5, count($m3Data['data']));
        foreach ($m3Data['data'] as $match) {
            $this->assertArrayHasKey('job_id', $match);
            $this->assertArrayHasKey('affinity_percentage', $match);
            $this->assertGreaterThanOrEqual(0.0, (float) $match['affinity_percentage']);
            $this->assertLessThanOrEqual(100.0, (float) $match['affinity_percentage']);
            $this->assertArrayHasKey('matching_skills', $match);
            $this->assertArrayHasKey('missing_skills', $match);
        }

        // ---------------------------------------------------------------------
        // Module 4 (W9D5): LangGraph 8-Week Learning Path Generator
        // ---------------------------------------------------------------------
        $m4Response = $this->actingAs($candidate)->postJson('/candidate/cia/career/learning-path', [
            'target_role' => $targetRole,
        ]);

        $m4Response->assertStatus(200);
        $m4Response->assertJsonStructure([
            'success',
            'candidate_id',
            'target_role',
            'total_weeks',
            'total_hours',
            'schedule' => [
                '*' => [
                    'week_number',
                    'theme',
                    'focus_skills',
                    'learning_objectives',
                    'actionable_tasks',
                    'estimated_hours',
                    'checkpoint_quiz',
                ],
            ],
            'workflow_metadata' => [
                'workflow_name',
                'nodes_executed',
                'engine',
                'is_valid',
                'log_id',
                'generated_at',
            ],
        ]);
        $m4Data = $m4Response->json();
        $this->assertTrue($m4Data['success']);
        $this->assertEquals(8, $m4Data['total_weeks']);
        $this->assertCount(8, $m4Data['schedule'], 'Learning path must produce exactly eight sequential weeks.');
        for ($i = 0; $i < 8; $i++) {
            $week = $m4Data['schedule'][$i];
            $this->assertEquals($i + 1, (int) $week['week_number']);
            $this->assertNotEmpty($week['theme']);
            $this->assertNotEmpty($week['focus_skills']);
            $this->assertNotEmpty($week['learning_objectives']);
            $this->assertNotEmpty($week['actionable_tasks']);
            $this->assertNotEmpty($week['checkpoint_quiz']);
            $this->assertGreaterThan(0, (int) $week['estimated_hours']);
        }
        $this->assertDatabaseHas('cia_search_logs', [
            'user_id' => $candidate->id,
            'query_type' => 'learning_path',
        ]);

        // ---------------------------------------------------------------------
        // Module 5 (W10D1): Resume ATS Intelligence & PDF Extraction
        // ---------------------------------------------------------------------
        $resumeText = "Arjun Mehta - Backend Engineer. " .
            "Technical proficiencies in PHP, Laravel, MySQL, REST APIs, Git, Docker, and PHPUnit. " .
            "Architected scalable microservices reducing API latency by 45% for 20000 active users. " .
            "Education: Bachelor of Computer Engineering. Contact: arjun.mehta@example.com";
        $pdfStream = $this->createValidPdf($resumeText);
        $resumeFile = UploadedFile::fake()->createWithContent('arjun_resume.pdf', $pdfStream);

        $m5Response = $this->actingAs($candidate)->post('/candidate/cia/career/resume/scan', [
            'resume' => $resumeFile,
            'target_role' => $targetRole,
        ], ['Accept' => 'application/json']);

        $m5Response->assertStatus(200);
        $m5Response->assertJsonStructure([
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
            'section_rewrites',
            'engine',
            'log_id',
        ]);
        $m5Data = $m5Response->json();
        $this->assertTrue($m5Data['success']);
        $this->assertGreaterThan(0, $m5Data['overall_ats_score']);
        $this->assertLessThanOrEqual(100, $m5Data['overall_ats_score']);
        $this->assertDatabaseHas('cia_search_logs', [
            'id' => $m5Data['log_id'],
            'user_id' => $candidate->id,
            'query_type' => 'resume_ats_score',
        ]);

        // ---------------------------------------------------------------------
        // Module 6 (W10D2): Multi-Metric Placement & Interview Readiness Scorecard
        // ---------------------------------------------------------------------
        $m6Response = $this->actingAs($candidate)->postJson('/candidate/cia/career/readiness/calculate', [
            'project_completion_score' => 88.0,
            'assessment_score' => 92.0,
            'cia_usage_score' => 85.0,
            'documentation_score' => 80.0,
            'viva_score' => 78.0,
            'code_review_score' => 84.0,
            'demo_clarity_score' => 86.0,
            'git_quality_score' => 90.0,
            'notes' => 'End-to-End verified candidate evaluation.',
        ]);

        $m6Response->assertStatus(200);
        $m6Response->assertJsonStructure([
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
        $m6Data = $m6Response->json('data');
        $this->assertGreaterThanOrEqual(0, $m6Data['placement_readiness_score']);
        $this->assertLessThanOrEqual(100, $m6Data['placement_readiness_score']);
        $this->assertGreaterThanOrEqual(0, $m6Data['interview_readiness_score']);
        $this->assertLessThanOrEqual(100, $m6Data['interview_readiness_score']);
        $this->assertDatabaseHas('placement_readiness_scores', [
            'id' => $m6Data['id'],
            'user_id' => $candidate->id,
        ]);

        // Verify GET /candidate/cia/career/readiness retrieves the persisted scorecard
        $m6GetResponse = $this->actingAs($candidate)->getJson('/candidate/cia/career/readiness');
        $m6GetResponse->assertStatus(200);
        $this->assertEquals($m6Data['id'], $m6GetResponse->json('data.id'));

        // ---------------------------------------------------------------------
        // Module 7 (W10D3): Career Intelligence Report & 6 Archetypes Synthesis
        // ---------------------------------------------------------------------
        $m7Response = $this->actingAs($candidate)->postJson('/candidate/cia/career/report/generate', []);

        $m7Response->assertStatus(200);
        $m7Response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'user_id',
                'report_type',
                'archetype_scores' => [
                    'Builder',
                    'Analyst',
                    'Leader',
                    'Innovator',
                    'Specialist',
                    'Generalist',
                ],
                'primary_archetype',
                'secondary_archetype',
                'employability_score',
                'narrative',
                'recommendations',
                'breakdown',
            ],
        ]);
        $m7Data = $m7Response->json('data');
        $this->assertEquals('career_intelligence', $m7Data['report_type']);
        $this->assertContains($m7Data['primary_archetype'], ['Builder', 'Analyst', 'Leader', 'Innovator', 'Specialist', 'Generalist']);
        $this->assertContains($m7Data['secondary_archetype'], ['Builder', 'Analyst', 'Leader', 'Innovator', 'Specialist', 'Generalist']);
        $this->assertNotEquals($m7Data['primary_archetype'], $m7Data['secondary_archetype']);
        $this->assertGreaterThanOrEqual(0.0, $m7Data['employability_score']);
        $this->assertLessThanOrEqual(100.0, $m7Data['employability_score']);
        $this->assertDatabaseHas('candidate_reports', [
            'id' => $m7Data['id'],
            'user_id' => $candidate->id,
        ]);

        // Verify latest report retrieval
        $m7Latest = $this->actingAs($candidate)->getJson('/candidate/cia/career/report/latest');
        $m7Latest->assertStatus(200);
        $this->assertEquals($m7Data['id'], $m7Latest->json('data.id'));

        // Verify executive PDF report download
        $m7Pdf = $this->actingAs($candidate)->get("/candidate/cia/career/report/{$m7Data['id']}/download");
        $m7Pdf->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $m7Pdf->headers->get('content-type'));

        // ---------------------------------------------------------------------
        // Module 8 (W10D4): CIA Memory Layer Role Matching (cynaris_role_memory)
        // ---------------------------------------------------------------------
        $m8Seed = $this->actingAs($candidate)->postJson('/candidate/cia/career/memory/seed', []);
        $m8Seed->assertStatus(200);
        $this->assertEquals(20, $m8Seed->json('data.seeded_count'));

        $m8Response = $this->actingAs($candidate)->getJson('/candidate/cia/career/memory/match?limit=5');
        $m8Response->assertStatus(200);
        $m8Response->assertJsonStructure([
            'success',
            'meta' => [
                'candidate_id',
                'total_matched',
                'limit',
                'engine',
                'collection',
            ],
            'data' => [
                '*' => [
                    'role_id',
                    'role',
                    'domain',
                    'experience_level',
                    'affinity_percentage',
                    'vector_distance',
                    'matching_skills',
                    'missing_skills',
                    'match_criteria',
                    'required_skills',
                ],
            ],
        ]);
        $m8Data = $m8Response->json();
        $this->assertTrue($m8Data['success']);
        $this->assertCount(5, $m8Data['data'], 'Must return top-5 matched role profiles.');
        foreach ($m8Data['data'] as $roleMatch) {
            $this->assertArrayHasKey('role_id', $roleMatch);
            $this->assertArrayHasKey('role', $roleMatch);
            $this->assertArrayHasKey('domain', $roleMatch);
            $this->assertArrayHasKey('affinity_percentage', $roleMatch);
            $this->assertGreaterThanOrEqual(0.0, (float) $roleMatch['affinity_percentage']);
            $this->assertLessThanOrEqual(100.0, (float) $roleMatch['affinity_percentage']);
            $this->assertArrayHasKey('matching_skills', $roleMatch);
            $this->assertArrayHasKey('missing_skills', $roleMatch);
        }

        // ---------------------------------------------------------------------
        // Cross-Module Continuity & Persistence Verification
        // ---------------------------------------------------------------------
        // Candidate now has accumulated search logs across multiple intelligence tools
        $searchLogsCount = CiaSearchLog::where('user_id', $candidate->id)->count();
        $this->assertGreaterThanOrEqual(4, $searchLogsCount);

        // Verify report synthesis reflects candidate's verified proficiencies
        $persistedReport = CandidateReport::where('user_id', $candidate->id)->latest()->first();
        $this->assertNotNull($persistedReport);
        $this->assertArrayHasKey('employability', $persistedReport->breakdown);
        $this->assertArrayHasKey('archetype_scores', $persistedReport->breakdown);
    }

    /**
     * Test 3: AI Provider Resilience: When Groq returns HTTP 429, 500, or is unconfigured,
     * the system degrades gracefully to deterministic heuristic fallback without crashing.
     */
    public function test_ai_provider_resilience_when_groq_api_fails_or_rate_limits(): void
    {
        $candidate = $this->createVerifiedCandidate('Pooja Patel', 'pooja.patel@example.com');

        // Simulate Groq Rate Limiting (HTTP 429) and Server Error (HTTP 500)
        Http::fake([
            'api.groq.com/*' => Http::sequence()
                ->push(['error' => 'Rate limit reached'], 429)
                ->push(['error' => 'Internal server error'], 500)
                ->push(['error' => 'Service unavailable'], 503),
        ]);

        config(['services.groq.key' => 'gsk_mock_fail_test']);

        // 1. Skill Gap resilience
        $sgRes = $this->actingAs($candidate)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);
        $sgRes->assertStatus(200);
        $this->assertEquals('deterministic_pipeline', $sgRes->json('engine'));

        // 2. Career Report resilience under rate limit
        $crRes = $this->actingAs($candidate)->postJson('/candidate/cia/career/report/generate', []);
        $crRes->assertStatus(200);
        $this->assertEquals('deterministic_heuristic_engine', $crRes->json('data.metadata.engine'));
        $this->assertNotEmpty($crRes->json('data.narrative'));

        // 3. Learning Path resilience
        $lpRes = $this->actingAs($candidate)->postJson('/candidate/cia/career/learning-path', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);
        $lpRes->assertStatus(200);
        $this->assertCount(8, $lpRes->json('schedule'));
    }

    /**
     * Test 4: ChromaDB Resilience: Validates vector collections, top-5 limits,
     * 0-100 affinity bounds, and deterministic fallback when vector engine is offline.
     */
    public function test_chromadb_resilience_across_both_collections(): void
    {
        $candidate = $this->createVerifiedCandidate('Kunal Joshi', 'kunal.joshi@example.com');

        // 1. Mock ChromaDbService offline fallback for cynaris_intern_profiles
        $mockChroma = $this->getMockBuilder(ChromaDbService::class)
            ->onlyMethods(['invokePythonEngine'])
            ->getMock();
        $mockChroma->method('invokePythonEngine')->willReturn(null);

        // Test syncProfile fallback targets cynaris_intern_profiles
        $syncFallback = $mockChroma->syncProfile($candidate);
        $this->assertTrue($syncFallback['success']);
        $this->assertEquals('cynaris_intern_profiles', $syncFallback['target_collection']);
        $this->assertEquals('fallback', $syncFallback['engine']);

        // Test matchInternships fallback
        $internFallback = $mockChroma->matchInternships($candidate, ['limit' => 5]);
        $this->assertTrue($internFallback['success']);
        $this->assertEquals('chromadb_fallback', $internFallback['meta']['engine']);
        $this->assertLessThanOrEqual(5, count($internFallback['data']));
        foreach ($internFallback['data'] as $job) {
            $this->assertGreaterThanOrEqual(0.0, (float) $job['affinity_percentage']);
            $this->assertLessThanOrEqual(100.0, (float) $job['affinity_percentage']);
            $this->assertIsArray($job['matching_skills']);
            $this->assertIsArray($job['missing_skills']);
        }

        // 2. Mock CiaMemoryLayerService offline fallback for cynaris_role_memory
        $mockMemory = $this->getMockBuilder(CiaMemoryLayerService::class)
            ->onlyMethods(['invokePythonEngine'])
            ->getMock();
        $mockMemory->method('invokePythonEngine')->willReturn(null);

        // Seeding fallback
        $seedFallback = $mockMemory->seedRoles();
        $this->assertTrue($seedFallback['success']);
        $this->assertEquals(20, $seedFallback['seeded_count']);
        $this->assertCount(20, $seedFallback['roles']);

        // Search fallback
        $roleFallback = $mockMemory->searchRoleMatches($candidate, ['limit' => 5]);
        $this->assertTrue($roleFallback['success']);
        $this->assertEquals('cynaris_role_memory', $roleFallback['meta']['collection']);
        $this->assertCount(5, $roleFallback['data']);
        foreach ($roleFallback['data'] as $role) {
            $this->assertArrayHasKey('role_id', $role);
            $this->assertGreaterThanOrEqual(0.0, (float) $role['affinity_percentage']);
            $this->assertLessThanOrEqual(100.0, (float) $role['affinity_percentage']);
            $this->assertIsArray($role['matching_skills']);
            $this->assertIsArray($role['missing_skills']);
        }
    }

    /**
     * Test 5: Validation Boundaries and Edge Cases across all modules.
     */
    public function test_validation_boundaries_and_edge_cases_across_modules(): void
    {
        $candidate = User::factory()->create();

        // 1. Skill Gap: Empty target role
        $this->actingAs($candidate)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => '',
        ])->assertStatus(422)->assertJsonValidationErrors(['target_role']);

        // 2. Resume Scan: Empty payload (no file)
        $this->actingAs($candidate)->postJson('/candidate/cia/career/resume/scan', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['resume']);

        // 3. Resume Scan: Non-PDF file
        $txtFile = UploadedFile::fake()->create('document.txt', 50, 'text/plain');
        $this->actingAs($candidate)->postJson('/candidate/cia/career/resume/scan', [
            'resume' => $txtFile,
        ])->assertStatus(422)->assertJsonValidationErrors(['resume']);

        // 4. Readiness Calculation: Out of bounds scores (> 100 or < 0)
        $this->actingAs($candidate)->postJson('/candidate/cia/career/readiness/calculate', [
            'project_completion_score' => 120.0,
            'assessment_score' => -10.0,
        ])->assertStatus(422)->assertJsonValidationErrors(['project_completion_score', 'assessment_score']);
    }
}
