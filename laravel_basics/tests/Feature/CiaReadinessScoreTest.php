<?php

namespace Tests\Feature;

use App\Models\CandidateLesson;
use App\Models\CiaSearchLog;
use App\Models\PlacementReadinessScore;
use App\Models\QuizScore;
use App\Models\User;
use App\Services\Readiness\PlacementReadinessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CiaReadinessScoreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Unauthenticated requests to readiness endpoints are rejected with 401.
     */
    public function test_unauthenticated_user_cannot_access_readiness_endpoints(): void
    {
        $this->postJson('/candidate/cia/career/readiness/calculate', [])->assertStatus(401);
        $this->getJson('/candidate/cia/career/readiness')->assertStatus(401);
    }

    /**
     * Test 2: Placement readiness formula verifies exact mathematical weighting:
     * Placement Readiness = (Project Completion * 0.40) + (Assessment * 0.30) + (CIA Usage * 0.10) + (Documentation * 0.20)
     */
    public function test_placement_readiness_formula_is_exact(): void
    {
        $user = User::factory()->create();
        $service = new PlacementReadinessService();

        $overrides = [
            'project_completion_score' => 90.0,
            'assessment_score' => 80.0,
            'cia_usage_score' => 70.0,
            'documentation_score' => 85.0,
        ];

        $record = $service->calculateScores($user, $overrides);

        // Expected: (90 * 0.40) + (80 * 0.30) + (70 * 0.10) + (85 * 0.20)
        // 36 + 24 + 7 + 17 = 84.0
        $expectedPlacement = 84.0;
        $this->assertEquals($expectedPlacement, $record->placement_readiness_score);
    }

    /**
     * Test 3: Interview readiness formula verifies exact mathematical weighting:
     * Interview Readiness = (Viva * 0.40) + (Code Review * 0.30) + (Demo Clarity * 0.20) + (Git Quality * 0.10)
     */
    public function test_interview_readiness_formula_is_exact(): void
    {
        $user = User::factory()->create();
        $service = new PlacementReadinessService();

        $overrides = [
            'viva_score' => 80.0,
            'code_review_score' => 85.0,
            'demo_clarity_score' => 75.0,
            'git_quality_score' => 90.0,
        ];

        $record = $service->calculateScores($user, $overrides);

        // Expected: (80 * 0.40) + (85 * 0.30) + (75 * 0.20) + (90 * 0.10)
        // 32.0 + 25.5 + 15.0 + 9.0 = 81.5
        $expectedInterview = 81.5;
        $this->assertEquals($expectedInterview, $record->interview_readiness_score);
    }

    /**
     * Test 4: Scores are clamped strictly between 0 and 100.
     */
    public function test_readiness_scores_are_clamped_strictly_between_0_and_100(): void
    {
        $service = new PlacementReadinessService();

        $this->assertEquals(0.0, $service->clampScore(-25.5));
        $this->assertEquals(100.0, $service->clampScore(125.0));
        $this->assertEquals(75.5, $service->clampScore(75.5));

        $user = User::factory()->create();
        $record = $service->calculateScores($user, [
            'project_completion_score' => 150.0,
            'assessment_score' => -20.0,
        ]);

        $this->assertLessThanOrEqual(100.0, $record->project_completion_score);
        $this->assertGreaterThanOrEqual(0.0, $record->assessment_score);
        $this->assertLessThanOrEqual(100.0, $record->placement_readiness_score);
        $this->assertGreaterThanOrEqual(0.0, $record->placement_readiness_score);
    }

    /**
     * Test 5: Safe execution for candidate with no coursework data.
     */
    public function test_readiness_service_handles_empty_coursework_gracefully(): void
    {
        $user = User::factory()->create();
        $service = new PlacementReadinessService();

        $record = $service->calculateScores($user);

        $this->assertNotNull($record);
        $this->assertGreaterThanOrEqual(0.0, $record->placement_readiness_score);
        $this->assertLessThanOrEqual(100.0, $record->placement_readiness_score);
        $this->assertGreaterThanOrEqual(0.0, $record->interview_readiness_score);
        $this->assertLessThanOrEqual(100.0, $record->interview_readiness_score);
    }

    /**
     * Test 6: API endpoint POST /candidate/cia/career/readiness/calculate persists score in database.
     */
    public function test_post_readiness_calculate_endpoint_persists_record(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/readiness/calculate', [
            'project_completion_score' => 88.0,
            'assessment_score' => 92.0,
            'cia_usage_score' => 80.0,
            'documentation_score' => 85.0,
            'viva_score' => 85.0,
            'code_review_score' => 90.0,
            'demo_clarity_score' => 80.0,
            'git_quality_score' => 95.0,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('placement_readiness_scores', [
            'user_id' => $user->id,
            'project_completion_score' => 88.0,
            'assessment_score' => 92.0,
        ]);
    }

    /**
     * Test 7: API endpoint GET /candidate/cia/career/readiness retrieves candidate score.
     */
    public function test_get_readiness_endpoint_retrieves_scorecard(): void
    {
        $user = User::factory()->create();

        // Create an existing score
        PlacementReadinessScore::create([
            'user_id' => $user->id,
            'project_completion_score' => 85.0,
            'assessment_score' => 80.0,
            'cia_usage_score' => 75.0,
            'documentation_score' => 90.0,
            'placement_readiness_score' => 83.5,
            'viva_score' => 80.0,
            'code_review_score' => 85.0,
            'demo_clarity_score' => 75.0,
            'git_quality_score' => 90.0,
            'interview_readiness_score' => 81.5,
            'breakdown' => ['status' => 'tested'],
            'status' => 'evaluated',
        ]);

        $response = $this->actingAs($user)->getJson('/candidate/cia/career/readiness');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'placement_readiness_score' => 83.5,
                'interview_readiness_score' => 81.5,
            ],
        ]);
    }
}

