# W11D2: Error Handling & Fallback Strategies Documentation
## Groq → OpenRouter → Deterministic Heuristic Architecture

**Project:** Cynaris Internship Portal (`laravel_basics`)  
**Sprint:** Week 11 Day 2 (W11D2)  
**Author:** Sachidananda Nayak  
**Branch:** `feat/cia-career-w11d2-sachidananda`  
**Base:** `origin/main` (`88baab9` with merged W11D1 PR #31)  

---

## 1. Executive Summary

In enterprise AI applications, high availability and resilient failure modes are critical requirements. Third-party LLM providers can experience rate limits, gateway timeouts, transient network partitions, or service degradation. 

Week 11 Day 2 centralizes the AI resilience architecture of the Cynaris Career Intelligence Advisor (CIA) subsystem into an enterprise-grade **Resilient AI Gateway** (`ResilientAiGateway` & `ResilientAiResponse`). The gateway orchestrates a deterministic **3-tier failover sequence**:
1. **Tier 1 (Primary):** Groq AI (`llama-3.3-70b-versatile`)
2. **Tier 2 (Secondary):** OpenRouter API (`meta-llama/llama-3.3-70b-instruct`)
3. **Tier 3 (Final Fallback):** Local algorithmic rule-based heuristic engines

### Key Engineering Advancements
- **Centralized Resilience Engine:** Eliminated duplicated HTTP client code, retry loops, and error-trapping across `SkillGapAnalysisService`, `CareerIntelligenceReportService`, and `ResumeAtsScoringService`.
- **Intelligent Fast-Failover:** Non-transient errors (HTTP 401 Unauthorized, 403 Forbidden, 422 Unprocessable) bypass retry loops and trigger immediate failover to the secondary provider, saving 200ms+ of wasted latency per request.
- **Transient Error Handling:** HTTP 429 (rate limits), 500, 502, 503, 504 and `ConnectionException` (timeouts, DNS failures) trigger controlled retries (2 attempts with 100ms backoff) and seamless failover.
- **Robust Markdown JSON Fence Stripping:** Universal regex parser safely extracts valid JSON from ````json ... ```` or ```` ... ```` codeblocks, eliminating parsing crashes from model conversational framing.
- **Zero-Trust Secret Redaction in Logs:** Automatically scrubs raw API keys (`gsk_*`, `sk-or-*`, `Bearer *`) from all log messages and context arrays before emitting to application log channels.
- **Resume ATS Secondary Provider Integration:** Enhanced `ResumeAtsScoringService` from a 2-tier system into the full 3-tier failover pipeline.
- **Comprehensive Test Coverage:** Expanded `CiaAiTimeoutFallbackTest.php` to **14 comprehensive tests with 52 assertions** covering all provider failure permutations.

---

## 2. Resilience Architecture & Provider Failover Sequence

### 2.1 Provider Hierarchy

```
                    +------------------------------------------------+
                    |  Client / Service Request                      |
                    |  (Skill Gap / Career Report / Resume ATS)      |
                    +-----------------------+------------------------+
                                            |
                                            v
                    +------------------------------------------------+
                    |        Tier 1: Groq AI Primary Provider        |
                    |  Model: llama-3.3-70b-versatile                |
                    |  Timeout: 10s | Retries: 2 (Transient only)   |
                    +-----------------------+------------------------+
                                            |
                         [Success (200 OK)] | [Failure: 4xx, 5xx, Timeout, Malformed]
                                +-----------+-----------+
                                |                       |
                                v                       v
                         (Return Payload)   +------------------------------------+
                                            |   Tier 2: OpenRouter Secondary     |
                                            |   Model: meta-llama/llama-3.3-70b  |
                                            |   Timeout: 15s | Retries: 2        |
                                            +-------------------+----------------+
                                                                |
                                             [Success (200 OK)] | [Failure / No Key]
                                                    +-----------+-----------+
                                                    |                       |
                                                    v                       v
                                             (Return Payload)   +------------------------------------+
                                                                |   Tier 3: Deterministic Fallback   |
                                                                |   Local Algorithmic Heuristic      |
                                                                |   Zero External HTTP Dependency    |
                                                                +-----------------+------------------+
                                                                                  |
                                                                                  v
                                                                           (Return Payload)
```

### 2.2 Provider Configuration Matrix (`config/services.php`)

| Provider Tier | Environment Key | Default Model | Default Endpoint | Default Timeout |
| :--- | :--- | :--- | :--- | :--- |
| **Tier 1 (Groq)** | `GROQ_API_KEY` | `llama-3.3-70b-versatile` | `https://api.groq.com/openai/v1/chat/completions` | 10 seconds |
| **Tier 2 (OpenRouter)** | `OPENROUTER_API_KEY` | `meta-llama/llama-3.3-70b-instruct` | `https://openrouter.ai/api/v1/chat/completions` | 15 seconds |
| **Tier 3 (Local)** | None | Deterministic Rule Engine | Local in-process memory | 0 ms |

---

## 3. Detailed Error Handling & Sanitization Policies

### 3.1 Fast-Failover vs. Retry Policies
The gateway differentiates between transient network glitches and deterministic client/auth failures:
- **Fast Failover (No Retries):**
  - **HTTP 401 Unauthorized:** Invalid, expired, or revoked API key. Retrying with the same key is guaranteed to fail. Fails over immediately to Tier 2.
  - **HTTP 403 Forbidden:** Access denied or insufficient project permissions. Fails over immediately.
  - **HTTP 422 Unprocessable Content:** Schema format rejected by provider. Fails over immediately.
- **Transient Failover (With Exponential Backoff):**
  - **HTTP 429 Too Many Requests:** Provider rate limit hit. Retries with 100ms backoff, then fails over to Tier 2.
  - **HTTP 500 / 502 / 503 / 504:** Upstream gateway/server errors. Retried up to 2 times, then fails over to Tier 2.
  - **`ConnectionException` (cURL 28):** Socket timeouts, connection refused, or DNS resolution failure. Retried up to 2 times, then fails over.

### 3.2 Markdown Code Fence Stripping
Many LLMs wrap valid JSON in markdown code blocks despite `response_format: {"type": "json_object"}` instructions. The gateway applies `stripMarkdownFences()`:
```php
public function stripMarkdownFences(string $content): string
{
    $clean = trim($content);

    if (str_starts_with($clean, '```')) {
        $clean = preg_replace('/^```(?:json|javascript|text)?\s*/i', '', $clean);
        $clean = preg_replace('/\s*```$/', '', $clean);
        $clean = trim($clean);
    }

    if (!str_starts_with($clean, '{') && !str_starts_with($clean, '[')) {
        if (preg_match('/```(?:json)?\s*(\{.*\}|\[.*\])\s*```/is', $clean, $matches)) {
            $clean = trim($matches[1]);
        } elseif (preg_match('/(\{.*\}|\[.*\])/is', $clean, $matches)) {
            $clean = trim($matches[1]);
        }
    }

    return $clean;
}
```

### 3.3 Log Sanitization & Credential Protection
To comply with security and compliance standards, raw API keys are never written to disk or centralized logging channels:
```php
public function redactSecrets(string $text): string
{
    $groqKey = (string) config('services.groq.key');
    $openRouterKey = (string) config('services.openrouter.key');

    $replacements = [];
    if (!empty($groqKey)) {
        $replacements[$groqKey] = '[REDACTED_GROQ_KEY]';
    }
    if (!empty($openRouterKey)) {
        $replacements[$openRouterKey] = '[REDACTED_OPENROUTER_KEY]';
    }

    $text = strtr($text, $replacements);

    // Pattern-based redactions
    $text = preg_replace('/Bearer\s+[a-zA-Z0-9_\-\.]+/i', 'Bearer [REDACTED]', $text);
    $text = preg_replace('/gsk_[a-zA-Z0-9]+/i', 'gsk_[REDACTED]', $text);
    $text = preg_replace('/sk-or-[a-zA-Z0-9_\-]+/i', 'sk-or-[REDACTED]', $text);

    return $text;
}
```

---

## 4. Service Integration Matrix

All CIA Career services have been upgraded to leverage `ResilientAiGateway` while strictly preserving their existing public signatures, database persistence, and API contract:

| Service | Primary Provider Engine | Secondary Provider Engine | Deterministic Fallback Engine | Custom Schema Validator |
| :--- | :--- | :--- | :--- | :--- |
| **`SkillGapAnalysisService`** | `groq_ai` | `openrouter_llama_3_3_70b` | `deterministic_pipeline` | Validates presence of `score` and `gaps` array |
| **`CareerIntelligenceReportService`** | `groq_llama_3_3_70b` | `openrouter_llama_3_3_70b` | `deterministic_heuristic_engine` | `validateNarrativePayload`: executive summary, archetype analysis, 90-day roadmap, strengths, growth areas, hiring pitch |
| **`ResumeAtsScoringService`** | `groq_llama3` | `openrouter_llama_3_3_70b` | `deterministic_ats_fallback` | `validateAtsPayload`: checks category breakdown scores (keyword matching, technical skills, experience, formatting) |

---

## 5. Test Matrix & Verification Results (`CiaAiTimeoutFallbackTest.php`)

The test suite exercises every provider failure permutation using Laravel's `Http::fake()` and mock exception injection:

| # | Test Method Name | Failure Condition Simulated | Expected Behavior | Result |
| :---: | :--- | :--- | :--- | :---: |
| 1 | `test_groq_success_does_not_call_openrouter` | Groq returns HTTP 200 with valid JSON | Returns `groq_ai`; OpenRouter is **NEVER** called | **PASS** |
| 2 | `test_groq_timeout_triggers_openrouter_fallback` | Groq throws `ConnectionException` (timeout) | Fails over to OpenRouter; returns `openrouter_llama_3_3_70b` | **PASS** |
| 3 | `test_groq_http_401_or_403_fast_failover_to_openrouter_without_retrying` | Groq returns HTTP 401 Unauthorized | Fails over immediately to OpenRouter with 0 useless retries | **PASS** |
| 4 | `test_groq_transient_http_errors_failover_to_openrouter` | Groq returns HTTP 503 Service Unavailable | Fails over to OpenRouter for Career Report narrative | **PASS** |
| 5 | `test_openrouter_timeout_falls_back_to_deterministic_engine` | Groq fails (500) and OpenRouter times out | Degrades gracefully to `deterministic_pipeline` with radar data | **PASS** |
| 6 | `test_openrouter_http_errors_fall_back_to_deterministic_engine` | Groq fails (429) and OpenRouter returns 502 | Degrades gracefully to `deterministic_pipeline` | **PASS** |
| 7 | `test_both_providers_unavailable_falls_back_cleanly` | Both Groq (504) and OpenRouter (524) fail | Both Skill Gap and Career Report degrade to deterministic heuristics | **PASS** |
| 8 | `test_missing_api_keys_falls_back_to_deterministic_without_http_calls` | Both `services.groq.key` and `services.openrouter.key` are null | Returns deterministic fallback; **0 HTTP requests dispatched** | **PASS** |
| 9 | `test_malformed_json_response_fails_over_gracefully` | Groq returns HTTP 200 with HTML error page | Malformed JSON detected; fails over cleanly to OpenRouter | **PASS** |
| 10 | `test_markdown_fenced_json_is_safely_parsed_without_fallback` | Groq returns JSON enclosed in ````json ... ```` | Fences stripped; parsed cleanly as `groq_ai`; OpenRouter not called | **PASS** |
| 11 | `test_empty_ai_response_content_triggers_openrouter_fallback` | Groq returns HTTP 200 with empty string content | Empty content trapped; fails over cleanly to OpenRouter | **PASS** |
| 12A | `test_resume_ats_scoring_groq_failover_to_openrouter` | Groq returns 429 rate limit during resume scan | Fails over to OpenRouter; returns `openrouter_llama_3_3_70b` | **PASS** |
| 12B | `test_resume_ats_scoring_dual_failover_to_deterministic` | Both Groq (timeout) and OpenRouter (500) fail | Fails over to `deterministic_ats_fallback` with keyword breakdown | **PASS** |
| 13 | `test_api_keys_are_redacted_from_logs_during_provider_failures` | Network failure occurs with sensitive credentials | Verifies raw API keys are redacted as `[REDACTED]` in log strings & contexts | **PASS** |

---

## 6. Verification Commands & Execution Logs

### 6.1 Fallback Test Suite Execution
```bash
php -d memory_limit=512M vendor/phpunit/phpunit/phpunit tests/Feature/CiaAiTimeoutFallbackTest.php --testdox
```
**Output:**
```text
Cia Ai Timeout Fallback (Tests\Feature\CiaAiTimeoutFallback)
 ✔ Groq success does not call openrouter
 ✔ Groq timeout triggers openrouter fallback
 ✔ Groq http 401 or 403 fast failover to openrouter without retrying
 ✔ Groq transient http errors failover to openrouter
 ✔ Openrouter timeout falls back to deterministic engine
 ✔ Openrouter http errors fall back to deterministic engine
 ✔ Both providers unavailable falls back cleanly
 ✔ Missing api keys falls back to deterministic without http calls
 ✔ Malformed json response fails over gracefully
 ✔ Markdown fenced json is safely parsed without fallback
 ✔ Empty ai response content triggers openrouter fallback
 ✔ Resume ats scoring groq failover to openrouter
 ✔ Resume ats scoring dual failover to deterministic
 ✔ Api keys are redacted from logs during provider failures

OK (14 tests, 52 assertions)
```

### 6.2 Full Application Test Suite Execution
```bash
php -d memory_limit=512M vendor/phpunit/phpunit/phpunit
```
**Output:**
```text
Tests: 192, Assertions: 1703, Passed: 192 (0 failures, 0 errors)
Time: 07:46.230
```

---

## 7. Git Commits & Change Summary

| Commit Hash | Commit Message | Files Included |
| :--- | :--- | :--- |
| `b80f0ce` | `feat(ai): centralize resilient AI gateway and improve fallback handling` | `laravel_basics/app/Services/Ai/ResilientAiGateway.php`<br>`laravel_basics/app/Services/Ai/ResilientAiResponse.php`<br>`laravel_basics/app/Services/SkillGapAnalysisService.php`<br>`laravel_basics/app/Services/CareerReport/CareerIntelligenceReportService.php`<br>`laravel_basics/app/Services/Resume/ResumeAtsScoringService.php`<br>`laravel_basics/config/services.php` |
| `539179c` | `test(ai): add comprehensive provider failure tests and documentation` | `laravel_basics/tests/Feature/CiaAiTimeoutFallbackTest.php`<br>`W11D2_ERROR_HANDLING_FALLBACK.md` |

---

## 8. Preserved Untracked Work Confirmation

The following pre-existing files and modules remain completely unmodified and preserved:
- `Moodle-Internship/`
- `espocrm/`
- `W5D1_API_DOCUMENTATION.md`
- `W7D1_MOODLE_SETUP_AND_ARCHITECTURE.md`
- `W8D1_AI_TUTOR_INTEGRATION.md`
- `test_publish_e2e.js`
