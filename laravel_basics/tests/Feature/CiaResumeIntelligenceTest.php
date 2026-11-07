<?php

namespace Tests\Feature;

use App\Models\CiaSearchLog;
use App\Models\Job;
use App\Models\User;
use App\Services\Resume\ResumeAtsScoringService;
use App\Services\Resume\ResumeTextExtractor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CiaResumeIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a minimal valid PDF containing readable plain text.
     */
    protected function createSamplePdf(string $text): string
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
     * Test 1: Unauthenticated request is rejected with 401.
     */
    public function test_unauthenticated_user_cannot_access_resume_scan(): void
    {
        $response = $this->postJson('/candidate/cia/career/resume/scan', []);

        $response->assertStatus(401);
    }

    /**
     * Test 2: Validation fails when no resume file is uploaded.
     */
    public function test_resume_scan_requires_file(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/resume/scan', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['resume']);
    }

    /**
     * Test 3: Validation fails when non-pdf file is uploaded.
     */
    public function test_resume_scan_rejects_non_pdf_files(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

        $response = $this->actingAs($user)->postJson('/candidate/cia/career/resume/scan', [
            'resume' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['resume']);
    }

    /**
     * Test 4: Valid PDF upload returns complete 4-category ATS scoring payload.
     */
    public function test_resume_scan_processes_valid_pdf_and_returns_scores(): void
    {
        $user = User::factory()->create();
        $sampleText = "Software Engineer with Bachelor of Engineering in Computer Science. " .
            "Proficient in PHP, Laravel, MySQL, REST APIs, Git, Docker, and PHPUnit. " .
            "Developed and optimized microservices architecture reducing API response latency by 45% for 10000 users. " .
            "Contact: candidate@example.com | 9876543210 | github.com/candidate";

        $pdfContent = $this->createSamplePdf($sampleText);
        $file = UploadedFile::fake()->createWithContent('resume.pdf', $pdfContent);

        $response = $this->actingAs($user)->post('/candidate/cia/career/resume/scan', [
            'resume' => $file,
            'target_role' => 'Junior Backend Laravel Engineer',
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
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

        $json = $response->json();
        $this->assertTrue($json['success']);
        $this->assertGreaterThan(0, $json['overall_ats_score']);
        $this->assertLessThanOrEqual(100, $json['overall_ats_score']);

        // Verify category scores
        $this->assertArrayHasKey('keyword_matching', $json['scores']);
        $this->assertArrayHasKey('technical_skills', $json['scores']);
        $this->assertArrayHasKey('work_experience', $json['scores']);
        $this->assertArrayHasKey('formatting', $json['scores']);

        // Verify keywords detection
        $this->assertContains('PHP', $json['matched_keywords']);
        $this->assertContains('Laravel', $json['matched_keywords']);
    }

    /**
     * Test 5: Resume scan results are persisted to database in cia_search_logs.
     */
    public function test_resume_scan_persists_audit_log_in_database(): void
    {
        $user = User::factory()->create();
        $sampleText = "Full Stack Engineer skilled in PHP, Laravel, MySQL, Vue.js, Tailwind CSS. " .
            "Designed and implemented high throughput booking platform. Contact: dev@example.com";

        $pdfContent = $this->createSamplePdf($sampleText);
        $file = UploadedFile::fake()->createWithContent('cv.pdf', $pdfContent);

        $response = $this->actingAs($user)->post('/candidate/cia/career/resume/scan', [
            'resume' => $file,
            'target_role' => 'Full Stack Developer',
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertDatabaseHas('cia_search_logs', [
            'id' => $json['log_id'],
            'user_id' => $user->id,
            'target_role' => 'Full Stack Developer',
            'query_type' => 'resume_ats_score',
            'status' => 'completed',
        ]);
    }

    /**
     * Test 6: Deterministic ATS engine correctly adheres to the 35/35/20/10 weight formula.
     */
    public function test_ats_scoring_service_applies_exact_category_weights(): void
    {
        $service = new ResumeAtsScoringService();
        $resumeText = "Software Engineer with experience in PHP, Laravel, MySQL, Docker, REST APIs, Git. " .
            "Developed scalable backend services reducing latency by 35%. " .
            "Education: Bachelor in Computer Science. Contact: engineer@cynaris.com";

        $result = $service->deterministicAtsScoring($resumeText, 'Junior Backend Laravel Engineer');

        $scores = $result['scores'];
        $expectedOverall = round(
            ($scores['keyword_matching'] * 0.35) +
            ($scores['technical_skills'] * 0.35) +
            ($scores['work_experience'] * 0.20) +
            ($scores['formatting'] * 0.10),
            1
        );

        $this->assertEquals($expectedOverall, $result['overall_ats_score']);
        $this->assertNotEmpty($result['improvement_suggestions']);
        $this->assertNotEmpty($result['section_rewrites']);
    }

    /**
     * Test 7: Resume text extractor works with sample file and handles unreadable paths.
     */
    public function test_resume_text_extractor_throws_exception_on_missing_file(): void
    {
        $extractor = new ResumeTextExtractor();

        $this->expectException(\RuntimeException::class);
        $extractor->extract('C:\\non_existent_directory\\fake_file.pdf');
    }
}
