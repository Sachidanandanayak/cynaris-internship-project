<?php

namespace Tests\Feature;

use App\Models\CandidateLesson;
use App\Models\CiaSearchLog;
use App\Models\QuizScore;
use App\Models\User;
use App\Services\CareerRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CiaCareerRecommendationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Unauthenticated request is rejected with 401.
     */
    public function test_unauthenticated_user_cannot_access_career_recommendations(): void
    {
        $response = $this->postJson('/candidate/cia/career/recommendations', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test 2: Endpoint returns exactly three career recommendations.
     */
    public function test_career_recommendations_endpoint_returns_three_paths(): void
    {
        $user = User::factory()->create();

        // Seed sample verified lessons and quiz
        CandidateLesson::create([
            'user_id' => $user->id,
            'title' => 'Laravel MVC Architecture',
            'skill' => 'Laravel',
            'is_completed' => true,
            'score' => 95.0,
            'completed_at' => now(),
        ]);

        QuizScore::create([
            'user_id' => $user->id,
            'quiz_title' => 'Laravel Mastery',
            'skill' => 'Laravel',
            'score' => 48.0,
            'max_score' => 50.0,
            'percentage' => 96.0,
            'passed' => true,
            'attempted_at' => now(),
        ]);

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/recommendations', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'target_role',
                'agent',
                'engine',
                'recommendations' => [
                    '*' => [
                        'title',
                        'fit_percentage',
                        'rationale',
                        'roadmap',
                    ],
                ],
                'log_id',
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals('CareerAdvisorAgent', $response->json('agent'));
        $this->assertCount(3, $response->json('recommendations'));
    }

    /**
     * Test 3: Recommendation results persist to cia_search_logs table.
     */
    public function test_career_recommendations_persist_to_cia_search_logs(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/recommendations', [
            'target_role' => 'Full Stack Developer (PHP & Vue/React)',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('cia_search_logs', [
            'user_id' => $user->id,
            'target_role' => 'Full Stack Developer (PHP & Vue/React)',
            'query_type' => 'career_recommendation',
            'status' => 'completed',
        ]);

        $log = CiaSearchLog::where('user_id', $user->id)->first();
        $this->assertNotNull($log);
        $this->assertIsArray($log->recommendations);
        $this->assertCount(3, $log->recommendations);
    }

    /**
     * Test 4: CareerAdvisorAgent service handles empty profile safely.
     */
    public function test_career_recommendations_handles_empty_profile_safely(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/recommendations', [
            'target_role' => 'Cloud & DevOps Intern',
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertCount(3, $response->json('recommendations'));
    }

    /**
     * Test 5: Fallback behavior when Python process or service throws.
     */
    public function test_career_recommendations_fallback_on_service_exception(): void
    {
        $user = User::factory()->create();

        // Bind mock service that simulates an error
        $mock = $this->partialMock(CareerRecommendationService::class, function ($mock) {
            $mock->shouldReceive('invokePythonAgent')->andReturn(null);
        });

        $this->app->instance(CareerRecommendationService::class, $mock);

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/recommendations', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertEquals('crewai_fallback', $response->json('engine'));
        $this->assertCount(3, $response->json('recommendations'));
    }

    /**
     * Test 6: UI section for CrewAI recommendations renders in CIA Career cockpit.
     */
    public function test_cia_career_tab_displays_crewai_section(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/candidate/cia/career');

        $response->assertStatus(200);
        $response->assertSee('Module 2: CrewAI Agent');
        $response->assertSee('Three Recommended Career Paths');
        $response->assertSee('recommendBtn');
    }
}
