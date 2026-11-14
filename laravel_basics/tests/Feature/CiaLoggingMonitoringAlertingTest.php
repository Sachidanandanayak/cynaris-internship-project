<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Ai\ResilientAiGateway;
use App\Services\Alerting\CiaAlertManager;
use App\Services\Logging\CiaLogger;
use App\Services\Monitoring\CiaMetricsService;
use App\Services\Vector\ChromaDbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tests\TestCase;

class CiaLoggingMonitoringAlertingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(CiaMetricsService::class)->reset();
        app(CiaAlertManager::class)->clearThrottles();
        Cache::flush();
    }

    /**
     * Helper to mock successful AI completion.
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
                                'score' => 88.0,
                                'gaps' => [],
                                'priority_learning' => [],
                                'radar' => [
                                    'labels' => ['PHP', 'Laravel', 'MySQL'],
                                    'candidate' => [85, 90, 80],
                                    'benchmark' => [80, 85, 75],
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
                                'score' => 88.0,
                                'gaps' => [],
                                'priority_learning' => [],
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);
    }

    /**
     * 1. Correlation ID Generation: Middleware attaches X-Correlation-ID to responses.
     */
    public function test_correlation_id_is_generated_and_propagated_to_response_headers(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Correlation-ID');
        $response->assertHeader('X-Request-ID');

        $correlationId = $response->headers->get('X-Correlation-ID');
        $this->assertTrue(Str::isUuid($correlationId), "Correlation ID must be a valid UUID.");
    }

    /**
     * 2. Correlation ID Propagation: Preserves client-supplied X-Correlation-ID.
     */
    public function test_incoming_correlation_id_is_preserved_and_returned_in_response(): void
    {
        $customId = 'cia-client-req-' . Str::random(16);

        $response = $this->withHeaders([
            'X-Correlation-ID' => $customId,
        ])->get('/login');

        $response->assertHeader('X-Correlation-ID', $customId);
        $response->assertHeader('X-Request-ID', $customId);
    }

    /**
     * 3. Structured Logging: CiaLogger formats log messages and context arrays.
     */
    public function test_structured_log_generation_with_correlation_id_and_operation_context(): void
    {
        $logger = app(CiaLogger::class);

        // Simulate request correlation attribute
        $req = Request::create('/candidate/cia/career/skill-gap', 'POST');
        $req->attributes->set('correlation_id', 'corr-test-uuid-999');
        app()->instance('request', $req);

        // Sanitize context
        $context = [
            'user_id' => 42,
            'target_role' => 'Junior Backend Laravel Engineer',
            'score' => 85.0,
        ];
        $sanitized = $logger->sanitizeContext($context);

        $this->assertEquals(42, $sanitized['user_id']);
        $this->assertEquals('Junior Backend Laravel Engineer', $sanitized['target_role']);
        $this->assertEquals(85.0, $sanitized['score']);
    }

    /**
     * 4. Security & Redaction: Secrets, emails, phones, and raw resume texts are redacted.
     */
    public function test_secret_and_personal_data_redaction_in_logs(): void
    {
        $logger = app(CiaLogger::class);

        $rawText = "Candidate john.doe@cynaris.com with phone +1-555-867-5309 used API key gsk_abc1234567890def and sk-or-v1-9876543210zyx Bearer secret_jwt_token_here.";
        $redactedText = $logger->redact($rawText);

        $this->assertStringNotContainsString('john.doe@cynaris.com', $redactedText);
        $this->assertStringContainsString('[REDACTED_EMAIL]', $redactedText);

        $this->assertStringNotContainsString('+1-555-867-5309', $redactedText);
        $this->assertStringContainsString('[REDACTED_PHONE]', $redactedText);

        $this->assertStringNotContainsString('gsk_abc1234567890def', $redactedText);
        $this->assertStringContainsString('gsk_[REDACTED]', $redactedText);

        $this->assertStringNotContainsString('sk-or-v1-9876543210zyx', $redactedText);
        $this->assertStringContainsString('sk-or-[REDACTED]', $redactedText);

        $this->assertStringNotContainsString('Bearer secret_jwt_token_here', $redactedText);
        $this->assertStringContainsString('Bearer [REDACTED]', $redactedText);

        // Context array redaction
        $context = [
            'password' => 'super_secret_password',
            'api_key' => 'gsk_raw_api_key',
            'resume_text' => 'CONFIDENTIAL RESUME CONTENT WITH EXPERIENCE DETAILS 1000 LINES',
            'normal_field' => 'Normal public data',
        ];
        $sanitizedContext = $logger->sanitizeContext($context);

        $this->assertEquals('[REDACTED]', $sanitizedContext['password']);
        $this->assertEquals('[REDACTED]', $sanitizedContext['api_key']);
        $this->assertStringContainsString('[REDACTED_RESUME_TEXT:', $sanitizedContext['resume_text']);
        $this->assertEquals('Normal public data', $sanitizedContext['normal_field']);
    }

    /**
     * 5. AI Provider Monitoring: Telemetry tracks failures, latency, and fallback counts.
     */
    public function test_ai_provider_failure_monitoring_and_fallback_tracking(): void
    {
        $metrics = app(CiaMetricsService::class);
        $gateway = app(ResilientAiGateway::class);

        config([
            'services.groq.key' => 'gsk_mock_fail_test',
            'services.openrouter.key' => 'sk-or-v1-mock_success_test',
        ]);

        // Groq fails with 500, OpenRouter succeeds with 200
        Http::fake([
            'https://api.groq.com/*' => Http::response(['error' => 'Groq Rate Limit Exceeded'], 500),
            'https://openrouter.ai/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode(['score' => 90.0]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $gateway->executeChat('system prompt', 'user prompt');

        $this->assertTrue($response->isSuccess());
        $this->assertEquals('openrouter', $response->getProvider());

        $health = $metrics->getHealthMetrics();
        $this->assertEquals(1, $health['ai_providers']['groq']['failures']);
        $this->assertEquals(1, $health['ai_providers']['openrouter']['success']);
        $this->assertEquals('degraded', $health['ai_providers']['groq']['status']);
        $this->assertEquals('healthy', $health['ai_providers']['openrouter']['status']);
    }

    /**
     * 6. Dual AI Failure Alerting: Alert triggered when both Groq and OpenRouter fail.
     */
    public function test_dual_ai_provider_failure_triggers_critical_alert(): void
    {
        $metrics = app(CiaMetricsService::class);
        $alerts = app(CiaAlertManager::class);
        $gateway = app(ResilientAiGateway::class);

        config([
            'services.groq.key' => 'gsk_mock_groq_dual',
            'services.openrouter.key' => 'sk-or-v1-mock_or_dual',
        ]);

        // Both providers fail
        Http::fake([
            'https://api.groq.com/*' => Http::response(['error' => 'Groq 503'], 503),
            'https://openrouter.ai/*' => Http::response(['error' => 'OpenRouter 502'], 502),
        ]);

        $response = $gateway->executeChat('system', 'user');

        $this->assertTrue($response->isFallback());

        $health = $metrics->getHealthMetrics();
        $this->assertEquals(1, $health['ai_providers']['dual_failure_count']);

        $recentAlerts = $alerts->getRecentAlerts();
        $this->assertNotEmpty($recentAlerts);
        $this->assertEquals(CiaAlertManager::ALERT_AI_DUAL_FAILURE, $recentAlerts[0]['alert_type']);
        $this->assertEquals('CRITICAL', $recentAlerts[0]['severity']);
    }

    /**
     * 7. ChromaDB Telemetry & Alert Threshold: Repeated failures mark status as unavailable and trigger alert.
     */
    public function test_chromadb_failure_logging_and_alert_threshold(): void
    {
        $metrics = app(CiaMetricsService::class);
        $alerts = app(CiaAlertManager::class);

        // Record 3 consecutive ChromaDB failures to reach threshold
        $metrics->recordChromaCall('sync_profile', false, 120.5, 'ChromaDB timeout', isFallback: true);
        $metrics->recordChromaCall('sync_profile', false, 95.0, 'ChromaDB connection refused', isFallback: true);
        $metrics->recordChromaCall('match_internships', false, 110.0, 'ChromaDB down', isFallback: true);

        $state = $metrics->getHealthMetrics();
        $this->assertEquals(3, $state['chromadb']['consecutive_failures']);
        $this->assertEquals('unavailable', $state['chromadb']['status']);

        // Check alert triggered
        $dispatched = $alerts->checkChromaFailureAlert(3);
        $this->assertTrue($dispatched);

        $recentAlerts = $alerts->getRecentAlerts();
        $this->assertNotEmpty($recentAlerts);
        $this->assertEquals(CiaAlertManager::ALERT_CHROMADB_UNAVAILABLE, $recentAlerts[0]['alert_type']);
    }

    /**
     * 8. Alert Deduplication / Throttling: Prevents alert storming within throttle window.
     */
    public function test_alert_deduplication_throttle_prevents_spam(): void
    {
        $alerts = app(CiaAlertManager::class);

        // First trigger should succeed and emit alert
        $firstResult = $alerts->trigger(
            CiaAlertManager::ALERT_AI_CONSECUTIVE_FAILURES,
            "Groq repeated failures",
            ['target' => 'groq']
        );
        $this->assertTrue($firstResult, "First alert must be dispatched.");

        // Immediate subsequent trigger with same alert type and target must be suppressed
        $secondResult = $alerts->trigger(
            CiaAlertManager::ALERT_AI_CONSECUTIVE_FAILURES,
            "Groq repeated failures",
            ['target' => 'groq']
        );
        $this->assertFalse($secondResult, "Second alert within throttle window must be suppressed.");

        // Verify only 1 alert recorded in recent alerts
        $this->assertCount(1, $alerts->getRecentAlerts());
    }

    /**
     * 9. Security: Unauthenticated access to monitoring endpoint returns 401.
     */
    public function test_unauthorized_access_to_monitoring_endpoint_is_rejected(): void
    {
        // Web JSON request
        $responseWeb = $this->getJson('/candidate/cia/career/monitoring/metrics');
        $responseWeb->assertStatus(401);

        // API Sanctum endpoint
        $responseApi = $this->getJson('/api/cia/monitoring/health');
        $responseApi->assertStatus(401);
    }

    /**
     * 10. Monitoring Endpoint: Authenticated candidate receives structured metrics payload.
     */
    public function test_authorized_candidate_receives_complete_monitoring_metrics(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/candidate/cia/career/monitoring/metrics');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'status',
            'timestamp',
            'correlation_id',
            'metrics' => [
                'requests' => ['total', 'successful', 'client_errors', 'server_errors', 'error_rate_percent'],
                'latency' => ['avg_ms', 'min_ms', 'max_ms', 'p95_ms'],
                'ai_providers' => [
                    'groq' => ['calls', 'success', 'failures', 'timeouts', 'status'],
                    'openrouter' => ['calls', 'success', 'failures', 'timeouts', 'status'],
                    'fallback_count',
                    'dual_failure_count',
                ],
                'chromadb' => ['calls', 'success', 'failures', 'status'],
                'exceptions' => ['total', 'recent'],
                'endpoints',
            ],
        ]);
        $response->assertHeader('X-Correlation-ID');
    }

    /**
     * 11. End-to-End Functional Regression: CIA endpoints record metrics and operate normally.
     */
    public function test_existing_cia_operations_continue_working_with_telemetry(): void
    {
        $this->fakeAiSuccess();
        $user = User::factory()->create();

        // Perform skill gap analysis
        $res = $this->actingAs($user)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);
        $res->assertStatus(200);
        $res->assertJson(['success' => true]);

        // Query metrics to verify request was tracked
        $metricsRes = $this->actingAs($user)->getJson('/candidate/cia/career/monitoring/metrics');
        $metricsRes->assertStatus(200);

        $metricsData = $metricsRes->json('metrics');
        $this->assertGreaterThanOrEqual(1, $metricsData['requests']['total']);
        $this->assertGreaterThanOrEqual(1, $metricsData['requests']['successful']);
        $this->assertEquals(0.0, $metricsData['requests']['error_rate_percent']);
    }
}
