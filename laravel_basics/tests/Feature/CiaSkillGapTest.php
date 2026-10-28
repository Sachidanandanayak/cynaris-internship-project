<?php

namespace Tests\Feature;

use App\Models\CandidateLesson;
use App\Models\CiaSearchLog;
use App\Models\QuizScore;
use App\Models\User;
use App\Services\SkillGapAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CiaSkillGapTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Unauthenticated user is rejected.
     */
    public function test_unauthenticated_user_cannot_access_skill_gap(): void
    {
        $response = $this->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test 2: Skill profile is correctly constructed from lessons and quizzes.
     */
    public function test_candidate_skill_profile_construction(): void
    {
        $user = User::factory()->create();

        // Seed 2 PHP lessons and 1 quiz
        CandidateLesson::create([
            'user_id' => $user->id,
            'title' => 'PHP 8 Basics',
            'skill' => 'PHP',
            'category' => 'Core',
            'is_completed' => true,
            'score' => 90.0,
            'completed_at' => now(),
        ]);

        CandidateLesson::create([
            'user_id' => $user->id,
            'title' => 'OOP in PHP',
            'skill' => 'PHP',
            'category' => 'Core',
            'is_completed' => true,
            'score' => 86.0,
            'completed_at' => now(),
        ]);

        QuizScore::create([
            'user_id' => $user->id,
            'quiz_title' => 'PHP Mastery Quiz',
            'skill' => 'PHP',
            'score' => 45.0,
            'max_score' => 50.0,
            'percentage' => 90.0,
            'passed' => true,
            'attempted_at' => now(),
        ]);

        $profile = $user->buildSkillProfile();

        $this->assertIsArray($profile);
        $this->assertNotEmpty($profile);
        $this->assertEquals('PHP', $profile[0]['skill']);
        $this->assertEquals(2, $profile[0]['lessons_completed']);
        $this->assertEquals(90.0, $profile[0]['quiz_average']);
        $this->assertEquals('Advanced', $profile[0]['proficiency_level']);
        $this->assertTrue($profile[0]['verified']);
    }

    /**
     * Test 3: Successful skill gap analysis via endpoint.
     */
    public function test_skill_gap_analysis_endpoint_returns_structured_json(): void
    {
        $user = User::factory()->create();

        // Seed lessons & quizzes
        CandidateLesson::create([
            'user_id' => $user->id,
            'title' => 'Laravel MVC Basics',
            'skill' => 'Laravel',
            'is_completed' => true,
            'score' => 95.0,
            'completed_at' => now(),
        ]);

        QuizScore::create([
            'user_id' => $user->id,
            'quiz_title' => 'Laravel Architecture',
            'skill' => 'Laravel',
            'score' => 48.0,
            'max_score' => 50.0,
            'percentage' => 96.0,
            'passed' => true,
            'attempted_at' => now(),
        ]);

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'target_role',
                'score',
                'gaps',
                'priority_learning',
                'skill_profile',
                'radar' => [
                    'labels',
                    'candidate_scores',
                    'required_scores',
                ],
                'engine',
                'log_id',
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertIsArray($response->json('gaps'));
        $this->assertIsArray($response->json('priority_learning'));
        $this->assertIsNumeric($response->json('score'));
    }

    /**
     * Test 4: Results persist into cia_search_logs table.
     */
    public function test_skill_gap_persists_to_cia_search_logs(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Full Stack Developer',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('cia_search_logs', [
            'user_id' => $user->id,
            'target_role' => 'Full Stack Developer',
            'query_type' => 'skill_gap',
            'status' => 'completed',
        ]);
    }

    /**
     * Test 5: Safe handling of candidate with no completed coursework.
     */
    public function test_skill_gap_handles_candidate_with_zero_data_safely(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertIsArray($response->json('gaps'));
        $this->assertNotEmpty($response->json('gaps'));
    }

    /**
     * Test 6: Input validation failures.
     */
    public function test_skill_gap_validates_target_role(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['target_role']);
    }

    /**
     * Test 7: CIA Career cockpit web view loads successfully.
     */
    public function test_cia_career_tab_view_renders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/candidate/cia/career');

        $response->assertStatus(200);
        $response->assertSee('CIA Career Mode');
        $response->assertSee('Competency Radar');
        $response->assertSee('skillRadarChart');
    }

    /**
     * Test 8: Groq API response validation and graceful fallback when mock fails.
     */
    public function test_groq_api_fallback_when_api_fails(): void
    {
        $user = User::factory()->create();

        config(['services.groq.key' => 'mock-groq-api-key']);

        // Mock Groq API failure (500)
        Http::fake([
            'api.groq.com/*' => Http::response(['error' => 'Rate limit exceeded'], 429),
        ]);

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertEquals('deterministic_pipeline', $response->json('engine'));
        $this->assertIsArray($response->json('gaps'));
    }

    /**
     * Test 9: Groq API response parsing when valid JSON is returned.
     */
    public function test_groq_api_success_with_valid_mock_json(): void
    {
        $user = User::factory()->create();

        config(['services.groq.key' => 'mock-groq-api-key']);

        $mockPayload = [
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'score' => 84.5,
                            'gaps' => [
                                [
                                    'skill' => 'Docker',
                                    'candidate_level' => 'Beginner',
                                    'required_level' => 'Intermediate',
                                    'impact' => 'High',
                                    'description' => 'Needs multi-stage container build experience.',
                                ]
                            ],
                            'priority_learning' => [
                                [
                                    'module' => 'Advanced Containerization',
                                    'priority' => 'High',
                                    'estimated_hours' => 5,
                                    'actionable_steps' => 'Configure multi-container compose environment.',
                                ]
                            ],
                        ]),
                    ],
                ],
            ],
        ];

        Http::fake([
            'api.groq.com/*' => Http::response($mockPayload, 200),
        ]);

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertEquals('groq_ai', $response->json('engine'));
        $this->assertEquals(84.5, $response->json('score'));
        $this->assertEquals('Docker', $response->json('gaps')[0]['skill']);
    }
}
