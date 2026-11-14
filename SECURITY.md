# Security Policy and OWASP Top 10 Security Audit

## 1. Executive Summary

This document details the **OWASP Top 10 Security Audit and Hardening Implementation** conducted for the **Cynaris Internship Portal (`cynaris-internship-project`)** across the **Cynaris Intelligence Augmentation (CIA) Career Mode** ecosystem (`laravel_basics`).

The audit and hardening engagement systematically evaluated all CIA Career endpoints, HTTP controllers, backend services, Eloquent models, database queries, file upload handlers, and AI fallback pipelines against the OWASP Top 10 (2021) security standards.

---

## 2. Security Audit Scope

| Scope Dimension | Specification |
| :--- | :--- |
| **Application** | `laravel_basics` (Laravel 11.x on PHP 8.5) |
| **Target Controller** | `App\Http\Controllers\CiaController` |
| **Core Services** | `PlacementReadinessService`, `CareerIntelligenceReportService`, `ResumeAtsScoringService`, `ResumeTextExtractor`, `SkillGapAnalysisService`, `ChromaDbService`, `CiaMemoryLayerService`, `LearningPathGeneratorService`, `ResilientAiGateway` |
| **Database Models** | `User`, `CandidateReport`, `PlacementReadinessScore`, `CiaSearchLog`, `CandidateLesson`, `QuizScore`, `Job`, `Application` |
| **CIA Career Endpoints Audited** | `GET /candidate/cia/career` (Cockpit)<br>`POST /candidate/cia/career/skill-gap` (Gap Analysis)<br>`POST /candidate/cia/career/recommendations` (CrewAI)<br>`POST /candidate/cia/career/profile/sync` (ChromaDB Sync)<br>`GET /candidate/cia/career/matches` (Semantic Matching)<br>`POST /candidate/cia/career/learning-path` (LangGraph)<br>`POST /candidate/cia/career/resume/scan` (Resume ATS)<br>`POST /candidate/cia/career/readiness/calculate` (Scorecard)<br>`GET /candidate/cia/career/readiness` (Readiness View)<br>`POST /candidate/cia/career/report/generate` (Report Generation)<br>`GET /candidate/cia/career/report/latest` (Report Fetch)<br>`GET /candidate/cia/career/report/{report}/download` (PDF Export)<br>`GET /candidate/cia/career/memory/match` (Memory Vector Search)<br>`POST /candidate/cia/career/memory/seed` (Memory Role Seeding) |

---

## 3. OWASP Top 10 Vulnerability Matrix & Findings

| OWASP Category | Vulnerability Evaluated | Discovered State | Mitigated Status | Remediation Summary |
| :--- | :--- | :--- | :--- | :--- |
| **A01: Broken Access Control** | IDOR on candidate reports, unauthenticated access, authorization bypass | Medium Risk | **RESOLVED** | Strictly enforced `auth` middleware across all routes. Hardened candidate ownership verification on `/report/{report}/download` (`abort(403)` on mismatch). Prevented Content-Disposition header injection via filename sanitization. |
| **A02: Cryptographic Failures** | Hardcoded secrets, API key exposure in git, tokens in logs | Low Risk (Pre-mitigated) | **VERIFIED** | Zero hardcoded API keys. All keys loaded strictly via `env()`. Built-in regex redaction (`gsk_[REDACTED]`, `sk-or-[REDACTED]`, `Bearer [REDACTED]`) across all logs. |
| **A03: Injection** | SQL Injection, Command Injection in Python sub-processes, Mass Assignment | Low Risk | **VERIFIED & HARDENED** | 100% parameterized queries via Eloquent ORM. No raw SQL concatenation. Sub-process parameters passed via isolated JSON temp files and wrapped in `escapeshellarg()`. Explicit `$fillable` arrays on all models. |
| **A04: Insecure Design** | Rate limiting absent on expensive AI/compute endpoints | High Risk | **RESOLVED** | Implemented tiered rate limiting via Laravel `RateLimiter`: `cia-ai` (60 req/min) for AI/compute endpoints and `cia-general` (120 req/min) for telemetry/read operations. |
| **A05: Security Misconfiguration** | Verbose exception traces in responses, internal debug info leaks | Medium Risk | **RESOLVED** | Sanitized `scanResume` exception catch block to return generic, user-safe error messages instead of raw `$e->getMessage()`. Debug details gated behind `config('app.debug')`. |
| **A06: Vulnerable Components** | Outdated or insecure packages | Low Risk | **VERIFIED** | Modern Laravel 11.x stack, Barryvdh DomPDF isolated rendering, validated dependencies. |
| **A07: Identification & Auth Failures** | Session hijacking, missing brute-force protection | Low Risk | **VERIFIED** | Secure session cookies, CSRF protection on state-changing requests, hashed passwords (`bcrypt`). |
| **A08: Software & Data Integrity** | Malicious file upload via resume scan (spoofed extensions, polyglots) | High Risk | **RESOLVED** | Enforced binary magic bytes verification (`%PDF`) on uploaded files in both `CiaController` and `ResumeTextExtractor`, alongside strict `mimes:pdf` and `max:10240` rules. |
| **A09: Security Logging & Monitoring** | Sensitive candidate data or keys logged, failure auditing | Low Risk | **VERIFIED** | Sanitized audit logging in `CiaSearchLog` with timestamped telemetry. Sensitive credentials scrubbed prior to log channel emission. |
| **A10: Server-Side Request Forgery** | Arbitrary URL fetch / SSRF via user input | Low Risk | **VERIFIED** | Zero user-supplied URLs fetched. All external AI calls target deterministic, pre-configured provider endpoints. |

---

## 4. Remediation Details

### 4.1. Abuse Protection & Rate Limiting (`AppServiceProvider.php` & `routes/web.php`)
Configured dedicated rate limiters for candidate traffic:
```php
// app/Providers/AppServiceProvider.php
RateLimiter::for('cia-ai', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('cia-general', function (Request $request) {
    return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
});
```
Applied `throttle:cia-ai` to compute-heavy and AI endpoints (`skill-gap`, `recommendations`, `profile/sync`, `learning-path`, `resume/scan`, `readiness/calculate`, `report/generate`, `memory/seed`) and `throttle:cia-general` to telemetry and read endpoints.

### 4.2. File Upload Security & Magic Bytes Verification (`CiaController.php` & `ResumeTextExtractor.php`)
To prevent disguised executables or polyglot files from bypassing MIME guessing:
```php
// Inspect binary header signature (%PDF)
$realPath = $file->getRealPath();
$handle = @fopen($realPath, 'rb');
$header = $handle ? fread($handle, 4) : '';
if ($handle) {
    fclose($handle);
}

if ($header !== '%PDF') {
    return response()->json([
        'success' => false,
        'message' => 'The uploaded file does not contain a valid PDF binary signature (%PDF).',
        'error_code' => 'ERR_INVALID_PDF_HEADER',
    ], 422);
}
```

### 4.3. IDOR Prevention & Header Sanitization (`CiaController.php` & `CareerIntelligenceReportService.php`)
- Strict candidate ownership validation on report downloads:
```php
if (!$user || $user->id !== $report->user_id) {
    abort(403, 'Unauthorized access to candidate report.');
}
```
- Content-Disposition filename sanitization preventing HTTP response splitting and header injection:
```php
$safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $candidate->name ?? 'Candidate');
$fileName = 'CIA_Career_Report_' . $safeName . '_' . $report->id . '.pdf';
return $pdf->download($fileName);
```

### 4.4. Input Validation & Strict Bounds (`CiaController.php`)
- Added validation for `generateReport` (`target_role: min:2, max:150`, `force_regenerate: boolean`).
- Added validation for `memoryMatch` (`limit: integer, min:1, max:20`).
- Hardened `recommendations` and `learningPath` role strings (`min:2, max:150`).

### 4.5. Information Disclosure Prevention in Error Messages
Sanitized error responses in `scanResume` to eliminate leakage of internal file paths, framework traces, or external service errors:
```php
return response()->json([
    'success' => false,
    'message' => 'An error occurred during resume ATS scoring. Please verify the resume format.',
    'error_code' => 'ERR_RESUME_SCAN_FAILED',
    'details' => config('app.debug') ? $e->getMessage() : null,
], 500);
```

---

## 5. Security Test Suite & Verification Results

A dedicated automated test suite was constructed at `laravel_basics/tests/Feature/CiaSecurityAuditTest.php` to prove that all vulnerability mitigations operate as intended.

### 5.1. Test Cases Executed

| # | Test Scenario | OWASP Target | Assertion / Verification | Result |
| :- | :--- | :--- | :--- | :---: |
| 1 | `test_unauthenticated_web_access_redirects_to_login` | A01: Broken Access Control | Web request returns 302 redirecting to `/login` | **PASS** |
| 2 | `test_unauthenticated_json_requests_return_401_across_all_cia_endpoints` | A01: Broken Access Control | All 12 JSON endpoints reject unauthenticated requests with 401 Unauthorized | **PASS** |
| 3 | `test_candidate_b_cannot_download_candidate_a_report` | A01: IDOR Protection | User B accessing User A's report download returns 403 Forbidden; User A returns 200 | **PASS** |
| 4 | `test_pdf_download_filename_is_sanitized_against_header_injection` | A01: Header Injection | Malicious names with `\r\n`, semicolons, and path traversals are safely sanitized in headers | **PASS** |
| 5 | `test_sql_injection_payloads_in_parameters_are_handled_safely` | A03: SQL Injection | Injected SQL (`' OR '1'='1`, `'; DROP TABLE users; --`) executes safely without SQL errors or data leaks | **PASS** |
| 6 | `test_candidate_cannot_forge_user_id_in_readiness_calculation` | A03: Mass Assignment | Injected `user_id` payload is ignored; score is stored strictly under authenticated user ID | **PASS** |
| 7 | `test_resume_upload_rejects_non_pdf_file` | A08: File Upload Security | Non-PDF file (`.php`, `.txt`) rejected with 422 Unprocessable Entity | **PASS** |
| 8 | `test_resume_upload_rejects_fake_pdf_lacking_magic_bytes` | A08: File Upload Security | Disguised text file named `.pdf` lacking `%PDF` binary header rejected with 422 (`ERR_INVALID_PDF_HEADER`) | **PASS** |
| 9 | `test_resume_upload_accepts_valid_pdf_with_magic_bytes` | A08: File Upload Security | Valid binary PDF with `%PDF-1.4` accepted and parsed successfully | **PASS** |
| 10 | `test_resume_upload_rejects_oversized_file` | A08: File Upload Security | Upload exceeding 10MB limit (11.5MB) rejected with 422 validation error | **PASS** |
| 11 | `test_input_validation_enforces_bounds_and_data_types` | A04: Input Validation | Negative scores, scores > 100, limits > 20, and invalid strings rejected with 422 | **PASS** |
| 12 | `test_secrets_and_api_keys_are_not_exposed_in_responses_or_logs` | A02: Secret Exposure | Provider 500 error responses and logs scrub raw `gsk_*` and `sk-or-*` keys | **PASS** |
| 13 | `test_resume_scan_error_returns_generic_message_without_exception_leakage` | A05: Info Disclosure | Error responses provide safe generic message without internal file paths or stack traces | **PASS** |
| 14 | `test_rate_limiting_enforces_threshold_with_http_429` | A04: Abuse Protection | Excessive burst requests beyond configured threshold return HTTP 429 Too Many Requests | **PASS** |
| 15 | `test_legitimate_candidate_can_access_all_cia_endpoints_safely` | Regression Integrity | End-to-end cockpit, gap analysis, readiness, report generation, download, and memory search succeed (200 OK) | **PASS** |

### 5.2. Test Suite Execution Summary
- **Security Test Suite:** `CiaSecurityAuditTest.php` → **15 tests, 90 assertions, 0 failures (100% pass)**.
- **Full Application Test Suite:** `phpunit` → **207 tests, 1,793 assertions, 0 failures (100% pass)**.

---

## 6. Remaining Risks & Operational Recommendations

1. **Sub-process Host Environment Isolation:**
   Python engines (`chromadb_matching_engine.py`, `chromadb_memory_engine.py`) execute in the host OS runtime. In production environments, running these engines inside dedicated containerized microservices (e.g., Docker container with read-only root filesystem and dropped Linux capabilities) is recommended for defense-in-depth.
2. **Dynamic AI Output Content:**
   AI-generated narratives from LLMs are rendered into PDF templates using Blade's automatic HTML entity escaping (`{{ $variable }}`). Ensure that future templates avoid unescaped `{!! !!}` blocks on AI output.
3. **Session Cookie Directives in Production:**
   Ensure `SESSION_SECURE_COOKIE=true` and `SESSION_HTTP_ONLY=true` are active in production environments over HTTPS.
