<?php

namespace Tests\Feature;

use App\Models\CandidateLesson;
use App\Models\CandidateReport;
use App\Models\CiaSearchLog;
use App\Models\PlacementReadinessScore;
use App\Models\QuizScore;
use App\Models\User;
use App\Services\CareerReport\CareerIntelligenceReportService;
use App\Services\Readiness\PlacementReadinessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CiaCareerIntelligenceReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Unauthenticated requests to report endpoints are rejected with 401.
     */
    public function test_unauthenticated_user_cannot_access_report_endpoints(): void
    {
        $this->postJson('/candidate/cia/career/report/generate', [])->assertStatus(401);
        $this->getJson('/candidate/cia/career/report/latest')->assertStatus(401);
    }

    /**
     * Test 2: Report generation computes 6 archetypes and resolves primary and secondary.
     */
    public function test_report_generation_evaluates_all_six_archetypes(): void
    {
        $user = User::factory()->create();

        CandidateLesson::create([
            'user_id' => $user->id,
            'title' => 'Laravel MVC Architecture',
            'skill' => 'Laravel',
            'is_completed' => true,
            'score' => 95.0,
        ]);

        QuizScore::create([
            'user_id' => $user->id,
            'quiz_title' => 'Laravel Mastery Quiz',
            'skill' => 'Laravel',
            'score' => 19,
            'max_score' => 20,
            'percentage' => 95.0,
        ]);

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/report/generate', []);

        $response->assertStatus(200);
        $response->assertJsonStructure([
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
                'breakdown',
            ],
        ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data['primary_archetype']);
        $this->assertArrayHasKey('Builder', $data['archetype_scores']);
        $this->assertArrayHasKey('Analyst', $data['archetype_scores']);
        $this->assertArrayHasKey('Leader', $data['archetype_scores']);
        $this->assertArrayHasKey('Innovator', $data['archetype_scores']);
        $this->assertArrayHasKey('Specialist', $data['archetype_scores']);
        $this->assertArrayHasKey('Generalist', $data['archetype_scores']);

        // Assert record persisted in candidate_reports
        $this->assertDatabaseHas('candidate_reports', [
            'id' => $data['id'],
            'user_id' => $user->id,
            'report_type' => 'career_intelligence',
        ]);
    }

    /**
     * Test 3: Employability score mathematical formula is exact:
     * Employability = (Primary Archetype * 0.25) + (Placement Readiness * 0.35) + (Interview Readiness * 0.25) + (Skill Average * 0.15)
     */
    public function test_employability_score_calculation_formula_is_exact(): void
    {
        $readinessService = new PlacementReadinessService();
        $reportService = new CareerIntelligenceReportService($readinessService);

        $primaryArchetypeScore = 80.0;
        $placementScore = 90.0;
        $interviewScore = 70.0;
        $skillProfile = [
            ['skill' => 'Laravel', 'proficiency_score' => 85.0],
            ['skill' => 'PHP', 'proficiency_score' => 75.0],
        ];

        // Skill avg = (85 + 75) / 2 = 80.0
        // Expected: (80.0 * 0.25) + (90.0 * 0.35) + (70.0 * 0.25) + (80.0 * 0.15)
        // 20.0 + 31.5 + 17.5 + 12.0 = 81.0
        $result = $reportService->calculateEmployabilityScore($primaryArchetypeScore, $placementScore, $interviewScore, $skillProfile);

        $this->assertEquals(81.0, $result['score']);
        $this->assertEquals('Competitive Candidate (Tier 2 Ready)', $result['status_tier']);
        $this->assertArrayHasKey('components', $result);
        $this->assertEquals(20.0, $result['components']['primary_archetype']['weighted']);
        $this->assertEquals(31.5, $result['components']['placement_readiness']['weighted']);
        $this->assertEquals(17.5, $result['components']['interview_readiness']['weighted']);
        $this->assertEquals(12.0, $result['components']['skill_average']['weighted']);
    }

    /**
     * Test 4: Employability and archetype scores are strictly clamped between 0 and 100.
     */
    public function test_scores_are_clamped_strictly_between_0_and_100(): void
    {
        $readinessService = new PlacementReadinessService();
        $reportService = new CareerIntelligenceReportService($readinessService);

        $this->assertEquals(100.0, $reportService->clampScore(125.0));
        $this->assertEquals(0.0, $reportService->clampScore(-15.0));
        $this->assertEquals(55.5, $reportService->clampScore(55.54));
    }

    /**
     * Test 5: Fallback narrative is generated gracefully when Groq API fails.
     */
    public function test_deterministic_narrative_fallback_when_groq_fails(): void
    {
        Http::fake([
            'api.groq.com/*' => Http::response(['error' => 'Rate limit exceeded'], 429),
        ]);

        $user = User::factory()->create(['name' => 'Aarav Sharma']);

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/report/generate', []);

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals('deterministic_heuristic_engine', $data['metadata']['engine']);
        $this->assertStringContainsString('Aarav Sharma', $data['narrative']);
        $this->assertCount(3, $data['metadata']['strengths']);
        $this->assertCount(3, $data['metadata']['growth_areas']);
        $this->assertCount(3, $data['metadata']['roadmap']);
    }

    /**
     * Test 6: Groq narrative generation parses structured JSON payload correctly.
     */
    public function test_groq_narrative_generation_succeeds_with_structured_mock(): void
    {
        config(['services.groq.key' => 'gsk_mock_test_key']);

        $mockJson = [
            'executive_summary' => 'Aarav is an exceptional systems builder with high placement readiness.',
            'archetype_analysis' => 'Demonstrates deep Builder tendencies with strong Analyst secondary skills.',
            'strengths' => ['API Architecture', 'Database Optimization', 'Clean Code PSR-12'],
            'growth_areas' => ['Technical Viva Defense', 'Automated Testing Breadth', 'Demo Delivery'],
            'roadmap_90_days' => [
                ['phase' => 'Month 1', 'timeframe' => 'Days 1-30', 'objective' => 'Core Backend', 'key_actions' => ['Refactor APIs', 'Unit Tests']],
                ['phase' => 'Month 2', 'timeframe' => 'Days 31-60', 'objective' => 'System Design', 'key_actions' => ['Dockerize App', 'Indexing']],
                ['phase' => 'Month 3', 'timeframe' => 'Days 61-90', 'objective' => 'Interviews', 'key_actions' => ['Mock Interviews', 'Portfolio']],
            ],
            'hiring_pitch' => 'Aarav is an elite junior backend engineer ready for high-scale microservices.',
        ];

        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => json_encode($mockJson)]],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/report/generate', []);

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals('groq_llama_3_3_70b', $data['metadata']['engine']);
        $this->assertStringContainsString('Aarav is an exceptional systems builder', $data['narrative']);
        $this->assertEquals('Aarav is an elite junior backend engineer ready for high-scale microservices.', $data['metadata']['hiring_pitch']);
    }

    /**
     * Test 7: Candidate can retrieve latest report via GET /candidate/cia/career/report/latest.
     */
    public function test_get_latest_report_endpoint_retrieves_record(): void
    {
        $user = User::factory()->create();

        $report = CandidateReport::create([
            'user_id' => $user->id,
            'report_type' => 'career_intelligence',
            'archetype_scores' => [
                'Builder' => 88.0,
                'Analyst' => 78.0,
                'Leader' => 70.0,
                'Innovator' => 65.0,
                'Specialist' => 82.0,
                'Generalist' => 74.0,
            ],
            'primary_archetype' => 'Builder',
            'secondary_archetype' => 'Specialist',
            'employability_score' => 84.5,
            'narrative' => 'Strong engineering builder with high potential.',
            'recommendations' => ['Focus on system design', 'Improve mock viva defense'],
            'breakdown' => ['employability' => ['status_tier' => 'Competitive Candidate']],
        ]);

        $response = $this->actingAs($user)->getJson('/candidate/cia/career/report/latest');

        $response->assertStatus(200);
        $this->assertEquals($report->id, $response->json('data.id'));
        $this->assertEquals(84.5, $response->json('data.employability_score'));
    }

    /**
     * Test 8: Candidate can download their report as PDF.
     */
    public function test_candidate_can_download_report_pdf(): void
    {
        $user = User::factory()->create(['name' => 'Priya Patel']);

        $report = CandidateReport::create([
            'user_id' => $user->id,
            'report_type' => 'career_intelligence',
            'archetype_scores' => [
                'Builder' => 85.0,
                'Analyst' => 80.0,
                'Leader' => 75.0,
                'Innovator' => 70.0,
                'Specialist' => 82.0,
                'Generalist' => 77.0,
            ],
            'primary_archetype' => 'Builder',
            'secondary_archetype' => 'Specialist',
            'employability_score' => 82.0,
            'narrative' => 'Executive narrative text for Priya Patel.',
            'recommendations' => ['Strength 1', 'Growth 1'],
            'breakdown' => [
                'employability' => ['status_tier' => 'Placement Ready (Tier 1 Priority)'],
                'narrative_details' => [
                    'executive_summary' => 'Executive summary for Priya Patel.',
                    'archetype_analysis' => 'Archetype analysis for Priya Patel.',
                    'strengths' => ['PHP Mastery', 'Laravel Eloquent', 'Clean Code'],
                    'growth_areas' => ['Technical Viva Defense', 'Demo Delivery'],
                    'roadmap_90_days' => [
                        ['phase' => 'Month 1', 'timeframe' => 'Days 1-30', 'objective' => 'Core Backend', 'key_actions' => ['Refactor APIs']],
                    ],
                    'hiring_pitch' => 'High potential backend engineer.',
                ],
            ],
        ]);

        $response = $this->actingAs($user)->get("/candidate/cia/career/report/{$report->id}/download");

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }

    /**
     * Test 9: Unauthorized user cannot download another candidate's report.
     */
    public function test_unauthorized_user_cannot_download_other_candidate_report(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $report = CandidateReport::create([
            'user_id' => $owner->id,
            'report_type' => 'career_intelligence',
            'archetype_scores' => ['Builder' => 80.0],
            'primary_archetype' => 'Builder',
            'employability_score' => 80.0,
            'narrative' => 'Confidential candidate report.',
            'recommendations' => [],
        ]);

        $response = $this->actingAs($stranger)->get("/candidate/cia/career/report/{$report->id}/download");
        $response->assertStatus(403);
    }

    /**
     * Test 10: Report generation persists audit telemetry log into cia_search_logs.
     */
    public function test_report_generation_persists_cia_search_log(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/candidate/cia/career/report/generate', []);

        $this->assertDatabaseHas('cia_search_logs', [
            'user_id' => $user->id,
            'query_type' => 'career_intelligence_report',
        ]);
    }
}
