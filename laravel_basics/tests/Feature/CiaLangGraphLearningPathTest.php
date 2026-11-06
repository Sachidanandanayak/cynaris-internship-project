<?php

namespace Tests\Feature;

use App\Models\CandidateLesson;
use App\Models\CiaSearchLog;
use App\Models\Job;
use App\Models\QuizScore;
use App\Models\User;
use App\Services\LearningPathGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CiaLangGraphLearningPathTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Unauthenticated request is rejected with 401.
     */
    public function test_unauthenticated_user_cannot_access_learning_path_generator(): void
    {
        $response = $this->postJson('/candidate/cia/career/learning-path', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test 2: Endpoint returns exactly eight sequential weeks with full schema.
     */
    public function test_learning_path_generator_returns_exactly_eight_sequential_weeks(): void
    {
        $user = User::factory()->create();

        // Seed verified lessons & quiz
        CandidateLesson::create([
            'user_id' => $user->id,
            'title' => 'Laravel MVC Architecture',
            'skill' => 'Laravel',
            'is_completed' => true,
            'score' => 92.0,
            'completed_at' => now(),
        ]);

        QuizScore::create([
            'user_id' => $user->id,
            'quiz_title' => 'Laravel Advanced Quiz',
            'skill' => 'Laravel',
            'score' => 48.0,
            'max_score' => 50.0,
            'percentage' => 96.0,
            'passed' => true,
            'attempted_at' => now(),
        ]);

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/learning-path', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'candidate_id',
                'target_role',
                'total_weeks',
                'total_hours',
                'baseline_tier',
                'primary_domain',
                'critical_gaps_addressed',
                'schedule' => [
                    '*' => [
                        'week_number',
                        'theme',
                        'focus_skills',
                        'learning_objectives',
                        'actionable_tasks',
                        'recommended_resources',
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

        $this->assertTrue($response->json('success'));
        $this->assertEquals(8, $response->json('total_weeks'));

        $schedule = $response->json('schedule');
        $this->assertCount(8, $schedule);

        // Verify sequential week ordering
        for ($i = 0; $i < 8; $i++) {
            $this->assertEquals($i + 1, $schedule[$i]['week_number']);
            $this->assertNotEmpty($schedule[$i]['theme']);
            $this->assertNotEmpty($schedule[$i]['focus_skills']);
            $this->assertNotEmpty($schedule[$i]['learning_objectives']);
            $this->assertNotEmpty($schedule[$i]['actionable_tasks']);
            $this->assertNotEmpty($schedule[$i]['checkpoint_quiz']);
        }
    }

    /**
     * Test 3: StateGraph traverses all five required workflow nodes in order.
     */
    public function test_learning_path_traverses_all_five_workflow_nodes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/learning-path', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200);

        $nodes = $response->json('workflow_metadata.nodes_executed');
        $this->assertIsArray($nodes);
        $this->assertEquals([
            'AssessState',
            'IdentifyGaps',
            'BuildPlan',
            'ValidatePlan',
            'OutputPlan',
        ], $nodes);
    }

    /**
     * Test 4: Weekly hours and total hours conform to required constraints (8-20h / 60-120h).
     */
    public function test_learning_path_weekly_and_total_hour_constraints(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/learning-path', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200);

        $totalHours = (int) $response->json('total_hours');
        $this->assertGreaterThanOrEqual(60, $totalHours);
        $this->assertLessThanOrEqual(120, $totalHours);

        $schedule = $response->json('schedule');
        $sumHours = 0;
        foreach ($schedule as $week) {
            $hours = (int) $week['estimated_hours'];
            $this->assertGreaterThanOrEqual(8, $hours, "Week {$week['week_number']} hours below 8h minimum");
            $this->assertLessThanOrEqual(20, $hours, "Week {$week['week_number']} hours above 20h maximum");
            $sumHours += $hours;
        }

        $this->assertEquals($totalHours, $sumHours);
    }

    /**
     * Test 5: Plan addresses candidate's critical skill gaps.
     */
    public function test_learning_path_addresses_critical_gaps(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/learning-path', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200);

        $gapsAddressed = $response->json('critical_gaps_addressed');
        $this->assertIsArray($gapsAddressed);
        $this->assertNotEmpty($gapsAddressed);
    }

    /**
     * Test 6: Learning path persists to cia_search_logs table.
     */
    public function test_learning_path_persists_to_cia_search_logs(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/learning-path', [
            'target_role' => 'Full Stack Developer (PHP & Vue/React)',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('cia_search_logs', [
            'user_id' => $user->id,
            'target_role' => 'Full Stack Developer (PHP & Vue/React)',
            'query_type' => 'learning_path',
            'status' => 'completed',
        ]);

        $log = CiaSearchLog::where('user_id', $user->id)->where('query_type', 'learning_path')->first();
        $this->assertNotNull($log);
        $this->assertCount(8, $log->recommendations);
        $this->assertEquals('LangGraphLearningPathWorkflow', $log->metadata['workflow_name']);
        $this->assertEquals(8, $log->metadata['total_weeks']);
    }

    /**
     * Test 7: Target job ID contextualizes the learning path.
     */
    public function test_learning_path_with_target_job_id(): void
    {
        $user = User::factory()->create();
        $job = Job::factory()->create([
            'title' => 'AI & LLM Integration Engineering Intern',
            'company' => 'Cynaris AI Research',
            'requirements' => 'Python, PHP, ChromaDB, Groq AI, and LlamaIndex.',
        ]);

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/learning-path', [
            'target_job_id' => $job->id,
        ]);

        $response->assertStatus(200);
        $this->assertEquals($job->id, $response->json('target_job_id'));
    }

    /**
     * Test 8: Graceful fallback when Python process or service throws.
     */
    public function test_learning_path_fallback_on_service_exception(): void
    {
        $user = User::factory()->create();

        // Bind mock service that simulates an error
        $mock = $this->partialMock(LearningPathGeneratorService::class, function ($mock) {
            $mock->shouldReceive('invokePythonGenerator')->andReturn(null);
        });

        $this->app->instance(LearningPathGeneratorService::class, $mock);

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/learning-path', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertEquals(8, $response->json('total_weeks'));
        $this->assertEquals('langgraph_fallback', $response->json('workflow_metadata.engine'));
    }

    /**
     * Test 9: UI section for LangGraph Learning Path renders in CIA Career cockpit.
     */
    public function test_cia_career_tab_displays_langgraph_section(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/candidate/cia/career');

        $response->assertStatus(200);
        $response->assertSee('Module 4: LangGraph Engine');
        $response->assertSee('Eight-Week Personalized Learning Path Generator');
        $response->assertSee('generatePathBtn');
    }
}

