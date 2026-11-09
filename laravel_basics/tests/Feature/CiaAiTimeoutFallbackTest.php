<?php

namespace Tests\Feature;

use App\Models\CandidateLesson;
use App\Models\QuizScore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CiaAiTimeoutFallbackTest extends TestCase
{
    use RefreshDatabase;

    protected function createTestCandidate(): User
    {
        $user = User::factory()->create([
            'name' => 'Aditya Verma',
            'email' => 'aditya.verma@example.com',
        ]);

        CandidateLesson::create([
            'user_id' => $user->id,
            'title' => 'Advanced PHP OOP',
            'skill' => 'PHP',
            'is_completed' => true,
            'score' => 90.0,
        ]);

        QuizScore::create([
            'user_id' => $user->id,
            'quiz_title' => 'PHP OOP Mastery',
            'skill' => 'PHP',
            'score' => 18,
            'max_score' => 20,
            'percentage' => 90.0,
        ]);

        return $user;
    }

    /**
     * Test 1: Groq timeout triggers OpenRouter secondary provider fallback.
     */
    public function test_groq_timeout_triggers_openrouter_fallback(): void
    {
        config([
            'services.groq.key' => 'gsk_test_mock_groq_key',
            'services.openrouter.key' => 'sk-or-v1-mock_openrouter_key',
        ]);

        $mockOpenRouterPayload = [
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'score' => 84.5,
                            'gaps' => [
                                [
                                    'skill' => 'Docker',
                                    'candidate_level' => 'Novice',
                                    'required_level' => 'Intermediate',
                                    'impact' => 'High',
                                    'description' => 'Containerization knowledge required for microservices.',
                                ],
                            ],
                            'priority_learning' => [
                                [
                                    'module' => 'Docker & Compose Mastery',
                                    'priority' => 'High',
                                    'estimated_hours' => 6.0,
                                    'actionable_steps' => 'Containerize multi-stage Laravel app.',
                                ],
                            ],
                        ]),
                    ],
                ],
            ],
        ];

        Http::fake([
            'api.groq.com/*' => function () {
                throw new ConnectionException('cURL error 28: Operation timed out after 5001 milliseconds');
            },
            'openrouter.ai/*' => Http::response($mockOpenRouterPayload, 200),
        ]);

        $candidate = $this->createTestCandidate();

        $response = $this->actingAs($candidate)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        // Verify OpenRouter was genuinely sent a request
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'openrouter.ai');
        });

        $this->assertEquals('openrouter_llama_3_3_70b', $data['engine']);
        $this->assertEquals(84.5, $data['score']);
        $this->assertCount(1, $data['gaps']);
        $this->assertEquals('Docker', $data['gaps'][0]['skill']);
    }

    /**
     * Test 2: Groq HTTP 429 rate limit triggers OpenRouter fallback for Career Report.
     */
    public function test_groq_rate_limit_triggers_openrouter_for_career_report(): void
    {
        config([
            'services.groq.key' => 'gsk_test_mock_groq_key',
            'services.openrouter.key' => 'sk-or-v1-mock_openrouter_key',
        ]);

        $mockNarrativePayload = [
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'executive_summary' => 'Aditya demonstrates solid backend systems knowledge with strong Builder tendencies.',
                            'archetype_analysis' => 'Exceptional primary Builder orientation paired with strong Specialist depth.',
                            'strengths' => ['API Architecture', 'Database Normalization', 'Clean Code'],
                            'growth_areas' => ['DevOps Deployment', 'Monitoring', 'Technical Viva Defense'],
                            'roadmap_90_days' => [
                                ['phase' => 'Month 1', 'timeframe' => 'Days 1-30', 'objective' => 'Core Backend', 'key_actions' => ['Refactor APIs']],
                                ['phase' => 'Month 2', 'timeframe' => 'Days 31-60', 'objective' => 'DevOps', 'key_actions' => ['Containerize']],
                                ['phase' => 'Month 3', 'timeframe' => 'Days 61-90', 'objective' => 'Interviews', 'key_actions' => ['Mock Interviews']],
                            ],
                            'hiring_pitch' => 'High-potential backend software engineer ready for production delivery.',
                        ]),
                    ],
                ],
            ],
        ];

        Http::fake([
            'api.groq.com/*' => Http::response(['error' => 'Rate limit exceeded (HTTP 429)'], 429),
            'openrouter.ai/*' => Http::response($mockNarrativePayload, 200),
        ]);

        $candidate = $this->createTestCandidate();

        $response = $this->actingAs($candidate)->postJson('/candidate/cia/career/report/generate', []);

        $response->assertStatus(200);
        $data = $response->json('data');

        // Verify OpenRouter was called
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'openrouter.ai');
        });

        $this->assertEquals('openrouter_llama_3_3_70b', $data['metadata']['engine']);
        $this->assertStringContainsString('Aditya demonstrates solid backend systems knowledge', $data['narrative']);
    }

    /**
     * Test 3: Groq HTTP 500 error triggers OpenRouter fallback.
     */
    public function test_groq_http_500_triggers_openrouter_fallback(): void
    {
        config([
            'services.groq.key' => 'gsk_test_mock_groq_key',
            'services.openrouter.key' => 'sk-or-v1-mock_openrouter_key',
        ]);

        $mockPayload = [
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'score' => 77.0,
                            'gaps' => [],
                            'priority_learning' => [],
                        ]),
                    ],
                ],
            ],
        ];

        Http::fake([
            'api.groq.com/*' => Http::response(['error' => 'Internal server error'], 500),
            'openrouter.ai/*' => Http::response($mockPayload, 200),
        ]);

        $candidate = $this->createTestCandidate();

        $response = $this->actingAs($candidate)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('openrouter_llama_3_3_70b', $response->json('engine'));
    }

    /**
     * Test 4: Both Groq and OpenRouter failing triggers deterministic heuristic fallback cleanly.
     */
    public function test_dual_provider_failure_engages_deterministic_fallback(): void
    {
        config([
            'services.groq.key' => 'gsk_test_mock_groq_key',
            'services.openrouter.key' => 'sk-or-v1-mock_openrouter_key',
        ]);

        Http::fake([
            'api.groq.com/*' => function () {
                throw new ConnectionException('Groq Gateway Timeout (504)');
            },
            'openrouter.ai/*' => function () {
                throw new ConnectionException('OpenRouter Upstream Timeout (524)');
            },
        ]);

        $candidate = $this->createTestCandidate();

        // 1. Skill Gap degrades to deterministic pipeline
        $sgResponse = $this->actingAs($candidate)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);
        $sgResponse->assertStatus(200);
        $this->assertEquals('deterministic_pipeline', $sgResponse->json('engine'));

        // 2. Career Report degrades to deterministic heuristic engine
        $crResponse = $this->actingAs($candidate)->postJson('/candidate/cia/career/report/generate', []);
        $crResponse->assertStatus(200);
        $this->assertEquals('deterministic_heuristic_engine', $crResponse->json('data.metadata.engine'));
    }

    /**
     * Test 5: Unconfigured OpenRouter key drops directly to deterministic fallback on Groq timeout.
     */
    public function test_unconfigured_openrouter_drops_directly_to_deterministic_on_groq_timeout(): void
    {
        config([
            'services.groq.key' => 'gsk_test_mock_groq_key',
            'services.openrouter.key' => null, // Not configured
        ]);

        Http::fake([
            'api.groq.com/*' => function () {
                throw new ConnectionException('Groq Read Timeout');
            },
        ]);

        $candidate = $this->createTestCandidate();

        $response = $this->actingAs($candidate)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('deterministic_pipeline', $response->json('engine'));

        // Assert OpenRouter was NEVER called
        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), 'openrouter.ai');
        });
    }
}

