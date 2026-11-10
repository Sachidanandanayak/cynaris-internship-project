<?php

namespace Tests\Feature;

use App\Models\CandidateLesson;
use App\Models\QuizScore;
use App\Models\User;
use App\Services\Ai\ResilientAiGateway;
use App\Services\Resume\ResumeAtsScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

    protected function sampleValidSkillGapPayload(float $score = 85.0): array
    {
        return [
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'score' => $score,
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
    }

    protected function sampleValidNarrativePayload(): array
    {
        return [
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
    }

    protected function sampleValidAtsPayload(): array
    {
        return [
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'overall_ats_score' => 88.5,
                            'scores' => [
                                'keyword_matching' => 90.0,
                                'technical_skills' => 88.0,
                                'work_experience' => 85.0,
                                'formatting' => 90.0,
                            ],
                            'matched_keywords' => ['PHP', 'Laravel', 'MySQL'],
                            'missing_keywords' => ['Docker'],
                            'improvement_suggestions' => ['Highlight containerization projects.'],
                            'section_rewrites' => [
                                [
                                    'section' => 'Experience',
                                    'current' => 'Wrote PHP code.',
                                    'rewritten' => 'Architected robust RESTful API endpoints in Laravel.',
                                ],
                            ],
                        ]),
                    ],
                ],
            ],
        ];
    }

    /**
     * Test 1: Groq success – OpenRouter is NOT called (Happy Path).
     */
    public function test_groq_success_does_not_call_openrouter(): void
    {
        config([
            'services.groq.key' => 'gsk_test_mock_groq_key',
            'services.openrouter.key' => 'sk-or-v1-mock_openrouter_key',
        ]);

        Http::fake([
            'api.groq.com/*' => Http::response($this->sampleValidSkillGapPayload(92.0), 200),
            'openrouter.ai/*' => Http::response(['error' => 'should not be called'], 500),
        ]);

        $candidate = $this->createTestCandidate();

        $response = $this->actingAs($candidate)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals('groq_ai', $data['engine']);
        $this->assertEquals(92.0, $data['score']);

        // Assert Groq was called, OpenRouter was NEVER called
        Http::assertSent(fn ($req) => str_contains($req->url(), 'api.groq.com'));
        Http::assertNotSent(fn ($req) => str_contains($req->url(), 'openrouter.ai'));
    }

    /**
     * Test 2: Groq timeout triggers OpenRouter secondary provider fallback.
     */
    public function test_groq_timeout_triggers_openrouter_fallback(): void
    {
        config([
            'services.groq.key' => 'gsk_test_mock_groq_key',
            'services.openrouter.key' => 'sk-or-v1-mock_openrouter_key',
        ]);

        Http::fake([
            'api.groq.com/*' => function () {
                throw new ConnectionException('cURL error 28: Operation timed out after 5001 milliseconds');
            },
            'openrouter.ai/*' => Http::response($this->sampleValidSkillGapPayload(84.5), 200),
        ]);

        $candidate = $this->createTestCandidate();

        $response = $this->actingAs($candidate)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        // Verify OpenRouter was genuinely sent a request
        Http::assertSent(fn ($req) => str_contains($req->url(), 'openrouter.ai'));

        $this->assertEquals('openrouter_llama_3_3_70b', $data['engine']);
        $this->assertEquals(84.5, $data['score']);
        $this->assertCount(1, $data['gaps']);
        $this->assertEquals('Docker', $data['gaps'][0]['skill']);
    }

    /**
     * Test 3: Groq HTTP 401 / 403 triggers fast failover to OpenRouter without retrying.
     */
    public function test_groq_http_401_or_403_fast_failover_to_openrouter_without_retrying(): void
    {
        config([
            'services.groq.key' => 'gsk_invalid_or_revoked_key',
            'services.openrouter.key' => 'sk-or-v1-mock_openrouter_key',
        ]);

        $groqRequestCount = 0;
        Http::fake([
            'api.groq.com/*' => function () use (&$groqRequestCount) {
                $groqRequestCount++;
                return Http::response(['error' => ['message' => 'Invalid API Key', 'type' => 'invalid_request_error', 'code' => 'invalid_api_key']], 401);
            },
            'openrouter.ai/*' => Http::response($this->sampleValidSkillGapPayload(81.0), 200),
        ]);

        $candidate = $this->createTestCandidate();

        $response = $this->actingAs($candidate)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('openrouter_llama_3_3_70b', $response->json('engine'));

        // Fast failover: Groq must only be called ONCE (no useless retrying on HTTP 401)
        $this->assertEquals(1, $groqRequestCount, 'Groq should not retry after HTTP 401.');
        Http::assertSent(fn ($req) => str_contains($req->url(), 'openrouter.ai'));
    }

    /**
     * Test 4: Groq transient HTTP errors (429, 500, 502, 503) failover to OpenRouter.
     */
    public function test_groq_transient_http_errors_failover_to_openrouter(): void
    {
        config([
            'services.groq.key' => 'gsk_test_mock_groq_key',
            'services.openrouter.key' => 'sk-or-v1-mock_openrouter_key',
        ]);

        Http::fake([
            'api.groq.com/*' => Http::response(['error' => '503 Service Unavailable'], 503),
            'openrouter.ai/*' => Http::response($this->sampleValidNarrativePayload(), 200),
        ]);

        $candidate = $this->createTestCandidate();

        $response = $this->actingAs($candidate)->postJson('/candidate/cia/career/report/generate', []);

        $response->assertStatus(200);
        $data = $response->json('data');

        Http::assertSent(fn ($req) => str_contains($req->url(), 'openrouter.ai'));
        $this->assertEquals('openrouter_llama_3_3_70b', $data['metadata']['engine']);
    }

    /**
     * Test 5: OpenRouter timeout falls back to deterministic fallback cleanly.
     */
    public function test_openrouter_timeout_falls_back_to_deterministic_engine(): void
    {
        config([
            'services.groq.key' => 'gsk_test_mock_groq_key',
            'services.openrouter.key' => 'sk-or-v1-mock_openrouter_key',
        ]);

        Http::fake([
            'api.groq.com/*' => Http::response(['error' => '500 Server Error'], 500),
            'openrouter.ai/*' => function () {
                throw new ConnectionException('cURL error 28: OpenRouter gateway timeout');
            },
        ]);

        $candidate = $this->createTestCandidate();

        $response = $this->actingAs($candidate)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('deterministic_pipeline', $response->json('engine'));
        $this->assertArrayHasKey('radar', $response->json());
    }

    /**
     * Test 6: OpenRouter HTTP errors fall back to deterministic engine.
     */
    public function test_openrouter_http_errors_fall_back_to_deterministic_engine(): void
    {
        config([
            'services.groq.key' => 'gsk_test_mock_groq_key',
            'services.openrouter.key' => 'sk-or-v1-mock_openrouter_key',
        ]);

        Http::fake([
            'api.groq.com/*' => Http::response(['error' => 'Groq Rate Limit'], 429),
            'openrouter.ai/*' => Http::response(['error' => 'OpenRouter 502 Bad Gateway'], 502),
        ]);

        $candidate = $this->createTestCandidate();

        $response = $this->actingAs($candidate)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('deterministic_pipeline', $response->json('engine'));
    }

    /**
     * Test 7: Both providers unavailable engages deterministic fallback across modules.
     */
    public function test_both_providers_unavailable_falls_back_cleanly(): void
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
     * Test 8: Missing API keys falls back to deterministic without making any HTTP calls.
     */
    public function test_missing_api_keys_falls_back_to_deterministic_without_http_calls(): void
    {
        config([
            'services.groq.key' => null,
            'services.openrouter.key' => null,
        ]);

        Http::fake();

        $candidate = $this->createTestCandidate();

        $response = $this->actingAs($candidate)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('deterministic_pipeline', $response->json('engine'));

        // Assert zero outbound HTTP requests were dispatched
        Http::assertNothingSent();
    }

    /**
     * Test 9: Malformed JSON response from Groq fails over gracefully to OpenRouter.
     */
    public function test_malformed_json_response_fails_over_gracefully(): void
    {
        config([
            'services.groq.key' => 'gsk_test_mock_groq_key',
            'services.openrouter.key' => 'sk-or-v1-mock_openrouter_key',
        ]);

        // Groq returns HTTP 200 with raw HTML error page instead of valid JSON
        $groqHtmlResponse = [
            'choices' => [
                [
                    'message' => [
                        'content' => '<html><body><h1>502 Bad Gateway</h1><p>Upstream proxy error.</p></body></html>',
                    ],
                ],
            ],
        ];

        Http::fake([
            'api.groq.com/*' => Http::response($groqHtmlResponse, 200),
            'openrouter.ai/*' => Http::response($this->sampleValidSkillGapPayload(82.0), 200),
        ]);

        $candidate = $this->createTestCandidate();

        $response = $this->actingAs($candidate)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('openrouter_llama_3_3_70b', $response->json('engine'));
        $this->assertEquals(82.0, $response->json('score'));
    }

    /**
     * Test 10: Markdown-fenced JSON is safely parsed without unnecessary fallback.
     */
    public function test_markdown_fenced_json_is_safely_parsed_without_fallback(): void
    {
        config([
            'services.groq.key' => 'gsk_test_mock_groq_key',
            'services.openrouter.key' => 'sk-or-v1-mock_openrouter_key',
        ]);

        $fencedContent = "```json\n" . json_encode([
            'score' => 88.5,
            'gaps' => [
                [
                    'skill' => 'Redis',
                    'candidate_level' => 'Novice',
                    'required_level' => 'Intermediate',
                    'impact' => 'Medium',
                    'description' => 'Caching mechanics.',
                ],
            ],
            'priority_learning' => [],
        ]) . "\n```";

        $mockPayload = [
            'choices' => [
                [
                    'message' => [
                        'content' => $fencedContent,
                    ],
                ],
            ],
        ];

        Http::fake([
            'api.groq.com/*' => Http::response($mockPayload, 200),
            'openrouter.ai/*' => Http::response(['error' => 'should not be called'], 500),
        ]);

        $candidate = $this->createTestCandidate();

        $response = $this->actingAs($candidate)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals('groq_ai', $data['engine']);
        $this->assertEquals(88.5, $data['score']);
        $this->assertEquals('Redis', $data['gaps'][0]['skill']);

        // Assert OpenRouter was not contacted
        Http::assertNotSent(fn ($req) => str_contains($req->url(), 'openrouter.ai'));
    }

    /**
     * Test 11: Empty AI response content triggers OpenRouter fallback.
     */
    public function test_empty_ai_response_content_triggers_openrouter_fallback(): void
    {
        config([
            'services.groq.key' => 'gsk_test_mock_groq_key',
            'services.openrouter.key' => 'sk-or-v1-mock_openrouter_key',
        ]);

        $mockEmptyPayload = [
            'choices' => [
                [
                    'message' => [
                        'content' => '',
                    ],
                ],
            ],
        ];

        Http::fake([
            'api.groq.com/*' => Http::response($mockEmptyPayload, 200),
            'openrouter.ai/*' => Http::response($this->sampleValidSkillGapPayload(79.0), 200),
        ]);

        $candidate = $this->createTestCandidate();

        $response = $this->actingAs($candidate)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('openrouter_llama_3_3_70b', $response->json('engine'));
        $this->assertEquals(79.0, $response->json('score'));
    }

    /**
     * Test 12A: Resume ATS Scoring Groq failover to OpenRouter.
     */
    public function test_resume_ats_scoring_groq_failover_to_openrouter(): void
    {
        $candidate = $this->createTestCandidate();
        $resumeService = app(ResumeAtsScoringService::class);
        $sampleResume = "Experienced PHP and Laravel Developer with 2 years of backend REST API engineering.";

        config([
            'services.groq.key' => 'gsk_test_mock_groq_key',
            'services.openrouter.key' => 'sk-or-v1-mock_openrouter_key',
        ]);

        Http::fake([
            'api.groq.com/*' => Http::response(['error' => 'Rate limit exceeded'], 429),
            'openrouter.ai/*' => Http::response($this->sampleValidAtsPayload(), 200),
        ]);

        $result = $resumeService->scoreResume($candidate, $sampleResume, 'Junior Backend Laravel Engineer');
        $this->assertEquals('openrouter_llama_3_3_70b', $result['engine']);
        $this->assertEquals(88.5, $result['overall_ats_score']);
    }

    /**
     * Test 12B: Resume ATS Scoring dual provider failover to deterministic fallback.
     */
    public function test_resume_ats_scoring_dual_failover_to_deterministic(): void
    {
        $candidate = $this->createTestCandidate();
        $resumeService = app(ResumeAtsScoringService::class);
        $sampleResume = "Experienced PHP and Laravel Developer with 2 years of backend REST API engineering.";

        config([
            'services.groq.key' => 'gsk_test_mock_groq_key',
            'services.openrouter.key' => 'sk-or-v1-mock_openrouter_key',
        ]);

        Http::fake([
            'api.groq.com/*' => function () {
                throw new ConnectionException('Groq cURL error 28 timeout');
            },
            'openrouter.ai/*' => Http::response(['error' => '500 Server Error'], 500),
        ]);

        $result = $resumeService->scoreResume($candidate, $sampleResume, 'Junior Backend Laravel Engineer');
        $this->assertEquals('deterministic_ats_fallback', $result['engine']);
        $this->assertGreaterThan(0.0, $result['overall_ats_score']);
        $this->assertArrayHasKey('matched_keywords', $result);
    }

    /**
     * Test 13: API keys are redacted from logs during provider failures.
     */
    public function test_api_keys_are_redacted_from_logs_during_provider_failures(): void
    {
        $mockGroqKey = 'gsk_super_secret_groq_key_99999';
        $mockOpenRouterKey = 'sk-or-v1-ultra_secret_openrouter_key_88888';

        config([
            'services.groq.key' => $mockGroqKey,
            'services.openrouter.key' => $mockOpenRouterKey,
        ]);

        $gateway = app(ResilientAiGateway::class);

        // Test string redaction directly
        $leakedMessage = "Failed authorization for Bearer {$mockGroqKey} and secondary {$mockOpenRouterKey}";
        $redactedMessage = $gateway->redactSecrets($leakedMessage);

        $this->assertStringNotContainsString($mockGroqKey, $redactedMessage);
        $this->assertStringNotContainsString($mockOpenRouterKey, $redactedMessage);
        $this->assertStringContainsString('[REDACTED_GROQ_KEY]', $redactedMessage);
        $this->assertStringContainsString('[REDACTED_OPENROUTER_KEY]', $redactedMessage);

        // Test context sanitization
        $leakedContext = [
            'auth' => "Bearer {$mockGroqKey}",
            'nested' => [
                'token' => $mockOpenRouterKey,
            ],
        ];
        $sanitizedContext = $gateway->sanitizeContext($leakedContext);

        $this->assertStringNotContainsString($mockGroqKey, $sanitizedContext['auth']);
        $this->assertStringNotContainsString($mockOpenRouterKey, $sanitizedContext['nested']['token']);
    }
}
