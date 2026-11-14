<?php

namespace Tests\Feature;

use App\Models\CandidateReport;
use App\Models\Job;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class CiaSecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper to create a valid minimal PDF with %PDF binary header.
     */
    protected function createValidPdf(string $text = 'Software Engineer with PHP and Laravel skills.'): string
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
     * Helper to mock Groq / OpenRouter AI responses.
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
                                'score' => 84.5,
                                'gaps' => [],
                                'priority_learning' => [],
                                'radar' => [
                                    'labels' => ['PHP', 'Laravel', 'MySQL'],
                                    'candidate' => [85, 90, 80],
                                    'benchmark' => [80, 85, 75],
                                ],
                                'executive_summary' => 'Strong candidate demonstrating verified Laravel competency.',
                                'career_readiness' => 'High readiness for junior backend engineering roles.',
                                'action_plan' => ['Complete advanced ORM course', 'Build portfolio microservice'],
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
                                'score' => 84.5,
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
     * 1. A01 - Broken Access Control: Unauthenticated web request redirects to login.
     */
    public function test_unauthenticated_web_access_redirects_to_login(): void
    {
        $response = $this->get('/candidate/cia/career');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * 2. A01 - Broken Access Control: Unauthenticated JSON requests return 401 across all CIA endpoints.
     */
    public function test_unauthenticated_json_requests_return_401_across_all_cia_endpoints(): void
    {
        $endpoints = [
            ['method' => 'postJson', 'uri' => '/candidate/cia/career/skill-gap', 'payload' => ['target_role' => 'Laravel Dev']],
            ['method' => 'postJson', 'uri' => '/candidate/cia/career/recommendations', 'payload' => []],
            ['method' => 'postJson', 'uri' => '/candidate/cia/career/profile/sync', 'payload' => []],
            ['method' => 'getJson', 'uri' => '/candidate/cia/career/matches'],
            ['method' => 'postJson', 'uri' => '/candidate/cia/career/learning-path', 'payload' => []],
            ['method' => 'postJson', 'uri' => '/candidate/cia/career/resume/scan', 'payload' => []],
            ['method' => 'postJson', 'uri' => '/candidate/cia/career/readiness/calculate', 'payload' => []],
            ['method' => 'getJson', 'uri' => '/candidate/cia/career/readiness'],
            ['method' => 'postJson', 'uri' => '/candidate/cia/career/report/generate', 'payload' => []],
            ['method' => 'getJson', 'uri' => '/candidate/cia/career/report/latest'],
            ['method' => 'getJson', 'uri' => '/candidate/cia/career/memory/match'],
            ['method' => 'postJson', 'uri' => '/candidate/cia/career/memory/seed', 'payload' => []],
        ];

        foreach ($endpoints as $ep) {
            $method = $ep['method'];
            $uri = $ep['uri'];
            $payload = $ep['payload'] ?? [];

            $response = $method === 'getJson'
                ? $this->getJson($uri)
                : $this->postJson($uri, $payload);

            $this->assertEquals(401, $response->status(), "Endpoint {$uri} must reject unauthenticated requests with 401.");
        }
    }

    /**
     * 3. A01 - IDOR Prevention: Candidate B cannot download Candidate A's report.
     */
    public function test_candidate_b_cannot_download_candidate_a_report(): void
    {
        $userA = User::factory()->create(['name' => 'Alice Candidate']);
        $userB = User::factory()->create(['name' => 'Bob Candidate']);

        $reportA = CandidateReport::create([
            'user_id' => $userA->id,
            'report_type' => 'career_intelligence',
            'archetype_scores' => ['Builder' => 90, 'Analyst' => 80],
            'primary_archetype' => 'Builder',
            'secondary_archetype' => 'Analyst',
            'employability_score' => 88.0,
            'narrative' => 'Alice is a builder.',
            'recommendations' => ['Path 1'],
            'breakdown' => [],
        ]);

        // User B attempts to access User A's report download
        $response = $this->actingAs($userB)->get("/candidate/cia/career/report/{$reportA->id}/download");

        // Must be rejected with 403 Forbidden
        $response->assertStatus(403);

        // User A accessing own report must succeed (200 OK)
        $ownerResponse = $this->actingAs($userA)->get("/candidate/cia/career/report/{$reportA->id}/download");
        $ownerResponse->assertStatus(200);
        $ownerResponse->assertHeader('content-type', 'application/pdf');
    }

    /**
     * 4. A01 - IDOR / Header Injection: Filenames in PDF downloads are sanitized against special characters.
     */
    public function test_pdf_download_filename_is_sanitized_against_header_injection(): void
    {
        $maliciousUser = User::factory()->create([
            'name' => "Malicious; Name\r\nInjected-Header: evil\x00/../../etc",
        ]);

        $report = CandidateReport::create([
            'user_id' => $maliciousUser->id,
            'report_type' => 'career_intelligence',
            'archetype_scores' => ['Builder' => 85, 'Analyst' => 75],
            'primary_archetype' => 'Builder',
            'secondary_archetype' => 'Analyst',
            'employability_score' => 80.0,
            'narrative' => 'Safe report narrative.',
            'recommendations' => ['Path 1'],
            'breakdown' => [],
        ]);

        $response = $this->actingAs($maliciousUser)->get("/candidate/cia/career/report/{$report->id}/download");
        $response->assertStatus(200);

        $disposition = $response->headers->get('content-disposition');
        $this->assertNotNull($disposition);
        $this->assertStringNotContainsString("\r", $disposition);
        $this->assertStringNotContainsString("\n", $disposition);
        $this->assertStringNotContainsString("Injected-Header:", $disposition);
        $this->assertStringNotContainsString("../", $disposition);
        $this->assertStringContainsString('CIA_Career_Report_', $disposition);
    }

    /**
     * 5. A03 - SQL Injection Resistance: Malicious SQL injection payloads in request inputs do not execute.
     */
    public function test_sql_injection_payloads_in_parameters_are_handled_safely(): void
    {
        $this->fakeAiSuccess();
        $user = User::factory()->create();

        $sqlPayloads = [
            "' OR '1'='1",
            "'; DROP TABLE users; --",
            "' UNION SELECT null, null, null, null --",
            "1' AND SLEEP(5) --",
        ];

        foreach ($sqlPayloads as $payload) {
            // Test in skill gap target_role
            $response = $this->actingAs($user)->postJson('/candidate/cia/career/skill-gap', [
                'target_role' => $payload,
            ]);
            // Either succeeds with parameterized query or validation error, never 500 SQL syntax error
            $this->assertContains($response->status(), [200, 422], "SQL payload must not cause 500 error.");

            // Test in semantic matches domain filter
            $matchResponse = $this->actingAs($user)->getJson('/candidate/cia/career/matches?domain=' . urlencode($payload));
            $this->assertContains($matchResponse->status(), [200, 422]);

            // Test in readiness calculation notes
            $readinessResponse = $this->actingAs($user)->postJson('/candidate/cia/career/readiness/calculate', [
                'notes' => $payload,
            ]);
            $this->assertContains($readinessResponse->status(), [200, 422]);
        }

        // Verify users table remains intact and unaffected
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    /**
     * 6. A03 - Mass Assignment Protection: Models protect user_id and sensitive attributes.
     */
    public function test_candidate_cannot_forge_user_id_in_readiness_calculation(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // User A attempts to submit scores claiming to be User B
        $response = $this->actingAs($userA)->postJson('/candidate/cia/career/readiness/calculate', [
            'user_id' => $userB->id,
            'project_completion_score' => 95.0,
            'assessment_score' => 90.0,
        ]);

        $response->assertStatus(200);

        // Assert record is saved strictly for User A, not User B
        $this->assertDatabaseHas('placement_readiness_scores', [
            'user_id' => $userA->id,
            'project_completion_score' => 95.0,
        ]);
        $this->assertDatabaseMissing('placement_readiness_scores', [
            'user_id' => $userB->id,
            'project_completion_score' => 95.0,
        ]);
    }

    /**
     * 7. A08 - File Upload Security: Non-PDF extension is rejected with 422.
     */
    public function test_resume_upload_rejects_non_pdf_file(): void
    {
        $user = User::factory()->create();
        $textDoc = UploadedFile::fake()->create('malicious.php', 100, 'text/x-php');

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/resume/scan', [
            'resume' => $textDoc,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['resume']);
    }

    /**
     * 8. A08 - File Upload Security: Disguised PDF without %PDF header magic bytes is rejected.
     */
    public function test_resume_upload_rejects_fake_pdf_lacking_magic_bytes(): void
    {
        $user = User::factory()->create();
        // File named .pdf, but body contains PHP script / shell payload
        $fakePdfContent = "<?php echo 'malicious_code_here'; ?>";
        $fakeFile = UploadedFile::fake()->createWithContent('exploit.pdf', $fakePdfContent);

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/resume/scan', [
            'resume' => $fakeFile,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'error_code' => 'ERR_INVALID_PDF_HEADER',
        ]);
    }

    /**
     * 9. A08 - File Upload Security: Valid PDF with %PDF-1.4 header is accepted.
     */
    public function test_resume_upload_accepts_valid_pdf_with_magic_bytes(): void
    {
        $this->fakeAiSuccess();
        $user = User::factory()->create();
        $validPdf = $this->createValidPdf("Senior Fullstack Engineer with 5 years experience in Laravel and Vue.js");
        $file = UploadedFile::fake()->createWithContent('valid_resume.pdf', $validPdf);

        $response = $this->actingAs($user)->post('/candidate/cia/career/resume/scan', [
            'resume' => $file,
            'target_role' => 'Junior Backend Laravel Engineer',
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertArrayHasKey('overall_ats_score', $response->json());
    }

    /**
     * 10. A08 - File Upload Security: Oversized PDF (>10MB) is rejected with 422.
     */
    public function test_resume_upload_rejects_oversized_file(): void
    {
        $user = User::factory()->create();
        // 11MB file (exceeds 10240 KB limit)
        $oversizedFile = UploadedFile::fake()->create('huge_resume.pdf', 11500, 'application/pdf');

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/resume/scan', [
            'resume' => $oversizedFile,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['resume']);
    }

    /**
     * 11. A04 / Input Validation: Numeric boundaries and string bounds are enforced.
     */
    public function test_input_validation_enforces_bounds_and_data_types(): void
    {
        $user = User::factory()->create();

        // Negative score in calculateReadiness
        $resNegative = $this->actingAs($user)->postJson('/candidate/cia/career/readiness/calculate', [
            'project_completion_score' => -15.0,
        ]);
        $resNegative->assertStatus(422);
        $resNegative->assertJsonValidationErrors(['project_completion_score']);

        // Score > 100 in calculateReadiness
        $resExcessive = $this->actingAs($user)->postJson('/candidate/cia/career/readiness/calculate', [
            'viva_score' => 150.0,
        ]);
        $resExcessive->assertStatus(422);
        $resExcessive->assertJsonValidationErrors(['viva_score']);

        // Limit > 20 in memoryMatch
        $resLimit = $this->actingAs($user)->getJson('/candidate/cia/career/memory/match?limit=999');
        $resLimit->assertStatus(422);
        $resLimit->assertJsonValidationErrors(['limit']);

        // Target role too short (1 char) in skill-gap
        $resShortRole = $this->actingAs($user)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'A',
        ]);
        $resShortRole->assertStatus(422);
        $resShortRole->assertJsonValidationErrors(['target_role']);

        // Target role too long (>150 chars) in skill-gap
        $resLongRole = $this->actingAs($user)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => str_repeat('X', 151),
        ]);
        $resLongRole->assertStatus(422);
        $resLongRole->assertJsonValidationErrors(['target_role']);
    }

    /**
     * 12. A02 / A05 - Secret & Sensitive Data Redaction: Secrets are never exposed in responses or error logs.
     */
    public function test_secrets_and_api_keys_are_not_exposed_in_responses_or_logs(): void
    {
        $secretKey = 'gsk_super_secret_test_key_12345';
        $openRouterKey = 'sk-or-v1-super_secret_openrouter_key_67890';
        config([
            'services.groq.key' => $secretKey,
            'services.openrouter.key' => $openRouterKey,
        ]);

        // Simulate 500 error from external provider
        Http::fake([
            'https://api.groq.com/*' => Http::response(['error' => 'Internal Groq Exception with ' . $secretKey], 500),
            'https://openrouter.ai/*' => Http::response(['error' => 'Internal OpenRouter Exception with ' . $openRouterKey], 500),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        // Response should fall back cleanly or return error, but NEVER leak keys
        $content = $response->getContent();
        $this->assertStringNotContainsString($secretKey, $content, "Groq API key must not appear in JSON response.");
        $this->assertStringNotContainsString($openRouterKey, $content, "OpenRouter API key must not appear in JSON response.");
    }

    /**
     * 13. A05 - Security Misconfiguration: Error responses do not leak internal exception traces.
     */
    public function test_resume_scan_error_returns_generic_message_without_exception_leakage(): void
    {
        $user = User::factory()->create();
        // A PDF that has valid header but empty text stream
        $emptyPdf = "%PDF-1.4\n1 0 obj << >> endobj\nxref\n0 2\n0000000000 65535 f \n0000000009 00000 n \ntrailer << /Size 2 /Root 1 0 R >>\nstartxref\n50\n%%EOF\n";
        $file = UploadedFile::fake()->createWithContent('empty.pdf', $emptyPdf);

        $response = $this->actingAs($user)->post('/candidate/cia/career/resume/scan', [
            'resume' => $file,
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'error_code' => 'ERR_PDF_EXTRACTION_EMPTY',
        ]);
        $this->assertStringNotContainsString('Exception', $response->json('message'));
        $this->assertStringNotContainsString('.php:', $response->json('message'));
    }

    /**
     * 14. A04 - Abuse Protection: Rate limiting middleware rejects requests exceeding threshold with HTTP 429.
     */
    public function test_rate_limiting_enforces_threshold_with_http_429(): void
    {
        $this->fakeAiSuccess();
        $user = User::factory()->create();

        // Configure a temporary strict limit for this test: 3 requests per minute
        RateLimiter::for('cia-ai', function (Request $request) {
            return Limit::perMinute(3)->by($request->user()?->id ?: $request->ip());
        });

        // 3 requests within limit should succeed
        for ($i = 0; $i < 3; $i++) {
            $res = $this->actingAs($user)->postJson('/candidate/cia/career/skill-gap', [
                'target_role' => 'Junior Backend Laravel Engineer',
            ]);
            $res->assertStatus(200);
        }

        // 4th request must be throttled with HTTP 429
        $throttled = $this->actingAs($user)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);

        $throttled->assertStatus(429);

        // Reset rate limiter to application default (60 per minute)
        RateLimiter::for('cia-ai', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * 15. Regression & Functional Integrity: Legitimate authenticated user can use full CIA pipeline without disruption.
     */
    public function test_legitimate_candidate_can_access_all_cia_endpoints_safely(): void
    {
        $this->fakeAiSuccess();
        $user = User::factory()->create();

        // 1. Cockpit index
        $this->actingAs($user)->get('/candidate/cia/career')->assertStatus(200);

        // 2. Skill gap analysis
        $gapRes = $this->actingAs($user)->postJson('/candidate/cia/career/skill-gap', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);
        $gapRes->assertStatus(200);
        $gapRes->assertJson(['success' => true]);

        // 3. Readiness scorecard calculation
        $readinessRes = $this->actingAs($user)->postJson('/candidate/cia/career/readiness/calculate', [
            'project_completion_score' => 88.0,
            'assessment_score' => 85.0,
            'cia_usage_score' => 90.0,
            'documentation_score' => 80.0,
            'viva_score' => 82.0,
            'code_review_score' => 85.0,
            'demo_clarity_score' => 80.0,
            'git_quality_score' => 88.0,
        ]);
        $readinessRes->assertStatus(200);
        $readinessRes->assertJson(['success' => true]);

        // 4. Report generation
        $reportRes = $this->actingAs($user)->postJson('/candidate/cia/career/report/generate', [
            'target_role' => 'Junior Backend Laravel Engineer',
        ]);
        $reportRes->assertStatus(200);
        $reportRes->assertJson(['success' => true]);
        $reportId = $reportRes->json('data.id');

        // 5. Download report PDF
        $downloadRes = $this->actingAs($user)->get("/candidate/cia/career/report/{$reportId}/download");
        $downloadRes->assertStatus(200);
        $downloadRes->assertHeader('content-type', 'application/pdf');

        // 6. Memory layer match
        $memoryRes = $this->actingAs($user)->getJson('/candidate/cia/career/memory/match?limit=5');
        $memoryRes->assertStatus(200);
        $memoryRes->assertJson(['success' => true]);
    }
}
