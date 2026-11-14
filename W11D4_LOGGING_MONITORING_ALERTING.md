# Week 11 Day 4 — Production Logging, Monitoring & Alerting Architecture

## 1. Executive Summary

This document specifies the production-grade **Logging, Monitoring, and Alerting architecture** implemented for the **Cynaris Intelligence Augmentation (CIA) Career Mode** (`cynaris-internship-project` / `laravel_basics`).

The implementation establishes end-to-end distributed observability across all CIA Career operations:
- **Structured Logging:** Unified `CiaLogger` enforcing consistent log levels (DEBUG, INFO, WARNING, ERROR), ISO 8601 timestamps, operation names, and zero-trust PII/secret scrubbing.
- **Request Tracing:** `CiaCorrelationIdMiddleware` propagating correlation IDs (`X-Correlation-ID` and `X-Request-ID`) across HTTP requests, Monolog global log context (`Log::shareContext`), and response headers.
- **Real-Time Monitoring:** In-memory and cache-backed `CiaMetricsService` tracking request counts, error rates, P95 response latencies, AI provider statuses (Groq, OpenRouter), and ChromaDB vector store health.
- **Critical Alerting:** Practical `CiaAlertManager` detecting dual AI provider outages, consecutive provider failures, vector store unavailabilities, and error rate spikes, with built-in alert deduplication (5-minute suppression window) and local structured log alerts.

---

## 2. Architecture Overview

```
                      +-----------------------------------+
                      |       Incoming HTTP Request       |
                      +-----------------+-----------------+
                                        |
                         (Extracts or generates UUID)
                                        v
                      +-----------------+-----------------+
                      |   CiaCorrelationIdMiddleware      |
                      |  - Sets X-Correlation-ID header   |
                      |  - Log::shareContext(['corr_id']) |
                      +-----------------+-----------------+
                                        |
                                        v
                      +-----------------+-----------------+
                      |         CiaController             |
                      +---+---------------------------+---+
                          |                           |
             (Invokes AI) |                           | (Vector Matching)
                          v                           v
              +-----------+----------+    +-----------+----------+
              |  ResilientAiGateway  |    |   ChromaDbService    |
              |  - Groq AI (Tier 1)  |    |  - Vector Sync       |
              |  - OpenRouter (Tier2)|    |  - Semantic Search   |
              |  - Deterministic (T3)|    |  - Role Memory Search|
              +-----------+----------+    +-----------+----------+
                          |                           |
                          +-------------+-------------+
                                        |
                  (Emits telemetry, metrics & alert triggers)
                                        v
    +-------------------+   +-----------+-----------+   +-------------------+
    |     CiaLogger     |   |   CiaMetricsService   |   |  CiaAlertManager  |
    | - Structured JSON |   | - Latencies & P95     |   | - Dual AI Outage  |
    | - PII Redaction   |   | - Provider Health     |   | - 3x Failures     |
    | - Secret Redaction|   | - Error Rates         |   | - Deduplication   |
    +-------------------+   +-----------+-----------+   +-------------------+
                                        |
                                        v
                      +-----------------+-----------------+
                      |     Monitoring Health API         |
                      |  /candidate/cia/career/monitoring |
                      +-----------------------------------+
```

---

## 3. Correlation ID & Distributed Request Tracing

### 3.1. Middleware Lifecycle (`CiaCorrelationIdMiddleware.php`)
Every HTTP request routed through the Cynaris portal passes through `App\Http\Middleware\CiaCorrelationIdMiddleware`:
1. **Extraction / Generation:** Checks for incoming `X-Correlation-ID` or `X-Request-ID` headers. If absent, generates an RFC 4122 compliant UUID via `(string) Str::uuid()`.
2. **Context Binding:** Sets `$request->attributes->set('correlation_id', $correlationId)`.
3. **Monolog Global Injection:** Registers the correlation ID globally with Laravel's logger via `Log::shareContext(['correlation_id' => $correlationId])`. Any log entry emitted during the request automatically includes the correlation ID.
4. **Header Propagation:** Injects `X-Correlation-ID` and `X-Request-ID` into the outgoing HTTP response headers.

---

## 4. Structured Logging & Zero-Trust Redaction (`CiaLogger.php`)

### 4.1. Log Levels & Operations
All CIA module operations write structured logs with standardized keys:
- **DEBUG:** Diagnostic telemetry (e.g., alert suppression events, payload length checks).
- **INFO:** Lifecycle milestones (e.g., `skill_gap.started`, `resume_ats_scan.completed`, `report.generated`).
- **WARNING:** Recoverable fallbacks (e.g., Groq API timeout falling back to OpenRouter, ChromaDB offline falling back to deterministic matching).
- **ERROR:** Unrecoverable or 500 exceptions.

### 4.2. Example Structured Log Entry
```json
{
  "message": "[CIA:skill_gap] Skill gap analysis completed successfully in 245.3ms",
  "context": {
    "cia_operation": "skill_gap",
    "correlation_id": "9f1a942a-c027-4ae0-9566-43cff41d1d46",
    "timestamp": "2026-11-14T10:45:00+00:00",
    "user_id": 42,
    "target_role": "Junior Backend Laravel Engineer",
    "score": 88.0,
    "engine": "groq_ai",
    "duration_ms": 245.3
  }
}
```

### 4.3. Data Protection & Redaction Policies
- **API Keys & Secrets:** Matches and masks raw tokens matching `gsk_*`, `sk-or-*`, and `Bearer *` (`[REDACTED]`).
- **Candidate PII:**
  - Email addresses replaced with `[REDACTED_EMAIL]`.
  - Phone numbers replaced with `[REDACTED_PHONE]`.
- **Confidential Resume Bodies:** Context keys containing `resume`, `extracted_text`, or `document_body` are never logged in full; they are sanitized to `[REDACTED_RESUME_TEXT: length={N}, sha256={hash}]`.
- **Payload Truncation:** Large string values are truncated to `config('cia.logging.max_payload_length', 300)` characters.

---

## 5. Real-Time Monitoring & Telemetry (`CiaMetricsService.php`)

`CiaMetricsService` maintains rolling in-memory and cache-backed operational counters:

### 5.1. Metrics Collected
1. **Request Telemetry:**
   - Total requests, successful requests (2xx), client errors (4xx), server errors (5xx).
   - Real-time error rate percentage (`failed / total * 100`).
2. **Latency Telemetry:**
   - Running average, minimum, maximum, and **P95 latency** computed across a rolling 100-request window.
3. **AI Provider Health:**
   - `groq`: Total calls, successes, failures, timeouts, consecutive failures, status (`healthy`, `degraded`, `unavailable`).
   - `openrouter`: Total calls, successes, failures, timeouts, consecutive failures, status.
   - `fallback_count`: Total fallbacks to deterministic synthesis.
   - `dual_failure_count`: Total occurrences of simultaneous Groq + OpenRouter outages.
4. **ChromaDB Vector Store Health:**
   - Total calls, successes, failures, consecutive failures, fallback executions, status (`healthy`, `degraded`, `unavailable`).
5. **Critical Exception Ledger:**
   - Rolling buffer of the last 10 sanitized exceptions with timestamps, operations, and exception classes.

### 5.2. Monitoring Endpoints
- **Web Endpoint:** `GET /candidate/cia/career/monitoring/metrics` (protected by `auth` middleware).
- **API Endpoint:** `GET /api/cia/monitoring/health` (protected by `auth:sanctum` middleware).
- **Unauthenticated Access:** Rejection with HTTP 401 Unauthorized.

#### Sample Monitoring Response (`200 OK`):
```json
{
  "success": true,
  "status": "healthy",
  "timestamp": "2026-11-14T10:48:00+00:00",
  "correlation_id": "eed20a99-9924-4bc0-875a-a9af2a09908b",
  "metrics": {
    "requests": {
      "total": 45,
      "successful": 44,
      "client_errors": 1,
      "server_errors": 0,
      "error_rate_percent": 0.0
    },
    "latency": {
      "avg_ms": 312.4,
      "min_ms": 45.2,
      "max_ms": 1120.0,
      "p95_ms": 890.5
    },
    "ai_providers": {
      "groq": { "calls": 20, "success": 20, "failures": 0, "timeouts": 0, "consecutive_failures": 0, "status": "healthy" },
      "openrouter": { "calls": 0, "success": 0, "failures": 0, "timeouts": 0, "consecutive_failures": 0, "status": "healthy" },
      "fallback_count": 0,
      "dual_failure_count": 0
    },
    "chromadb": {
      "calls": 12,
      "success": 12,
      "failures": 0,
      "consecutive_failures": 0,
      "fallback_count": 0,
      "status": "healthy"
    },
    "exceptions": {
      "total": 0,
      "recent": []
    }
  }
}
```

---

## 6. Critical Alerting & Deduplication (`CiaAlertManager.php`)

### 6.1. Alert Trigger Conditions
| Alert Type | Severity | Trigger Condition | Default Threshold |
| :--- | :---: | :--- | :---: |
| `AI_DUAL_FAILURE` | CRITICAL | Both Groq (Tier 1) and OpenRouter (Tier 2) fail in a single request | 1 occurrence |
| `AI_CONSECUTIVE_FAILURES` | CRITICAL | Provider records repeated consecutive failures | >= 3 consecutive failures |
| `CHROMADB_UNAVAILABLE` | CRITICAL | ChromaDB vector store fails consecutively | >= 3 consecutive failures |
| `HIGH_ERROR_RATE` | CRITICAL | 5xx error rate across CIA endpoints exceeds threshold (min 10 reqs) | >= 25.0% |
| `CRITICAL_EXCEPTION` | CRITICAL | Unhandled fatal or database exception in CIA controllers | 1 occurrence |

### 6.2. Alert Deduplication / Throttling Window
To prevent alert storming and log exhaustion during extended outages:
- Every alert type and target combination is throttled via `Cache::put("cia_alert_throttle:{hash}", ..., $throttleSeconds)`.
- Default suppression window: **300 seconds (5 minutes)**.
- Repeated triggers within the window are safely logged at DEBUG level (`[CIA_ALERT_SUPPRESSED]`) and discarded without duplicating alerts.

### 6.3. Safe Local Dispatch
- Dispatches structured logs: `Log::critical("[CIA_ALERT:{$alertType}] {$summary}", $payload)`.
- Saves structured alert payloads to `cia_recent_alerts` cache buffer for administrative inspection.
- Optional webhook dispatch (`CIA_ALERT_WEBHOOK_URL`) wrapped in a 3-second non-blocking timeout.

---

## 7. Environment Configuration (`config/cia.php`)

| Environment Variable | Config Path | Default Value | Description |
| :--- | :--- | :---: | :--- |
| `CIA_LOGGING_ENABLED` | `cia.logging.enabled` | `true` | Toggles structured CIA logging |
| `CIA_LOG_CHANNEL` | `cia.logging.channel` | `'single'` | Log channel for CIA events |
| `CIA_LOG_REDACT_PII` | `cia.logging.redact_pii` | `true` | Enables automatic email and phone number scrubbing |
| `CIA_LOG_MAX_PAYLOAD` | `cia.logging.max_payload_length` | `300` | Maximum character length for logged context strings |
| `CIA_MONITORING_ENABLED` | `cia.monitoring.enabled` | `true` | Toggles telemetry and latency sampling |
| `CIA_ALERTING_ENABLED` | `cia.alerting.enabled` | `true` | Toggles alerting manager triggers |
| `CIA_ALERT_CHANNEL` | `cia.alerting.channel` | `'single'` | Target channel for `[CIA_ALERT]` log entries |
| `CIA_ALERT_THROTTLE_SECONDS` | `cia.alerting.throttle_seconds` | `300` | Alert deduplication window in seconds (5 minutes) |
| `CIA_ALERT_AI_FAILURES_THRESHOLD` | `cia.alerting.thresholds.consecutive_ai_failures` | `3` | Consecutive AI failures before triggering alert |
| `CIA_ALERT_CHROMA_FAILURES_THRESHOLD` | `cia.alerting.thresholds.consecutive_chroma_failures` | `3` | Consecutive ChromaDB failures before triggering alert |
| `CIA_ALERT_ERROR_RATE_THRESHOLD` | `cia.alerting.thresholds.error_rate_percent` | `25.0` | Percentage 5xx threshold for alerting |
| `CIA_ALERT_WEBHOOK_URL` | `cia.alerting.webhook_url` | `null` | Optional external notification webhook URL |

---

## 8. Verification & Test Suite Results

### 8.1. Dedicated Feature Test Suite (`CiaLoggingMonitoringAlertingTest.php`)
```bash
php -d memory_limit=512M vendor/phpunit/phpunit/phpunit tests/Feature/CiaLoggingMonitoringAlertingTest.php --testdox
```
```text
Cia Logging Monitoring Alerting (Tests\Feature\CiaLoggingMonitoringAlerting)
 ✔ Correlation id is generated and propagated to response headers
 ✔ Incoming correlation id is preserved and returned in response
 ✔ Structured log generation with correlation id and operation context
 ✔ Secret and personal data redaction in logs
 ✔ Ai provider failure monitoring and fallback tracking
 ✔ Dual ai provider failure triggers critical alert
 ✔ Chromadb failure logging and alert threshold
 ✔ Alert deduplication throttle prevents spam
 ✔ Unauthorized access to monitoring endpoint is rejected
 ✔ Authorized candidate receives complete monitoring metrics
 ✔ Existing cia operations continue working with telemetry

OK (11 tests, 95 assertions)
```

### 8.2. Full Regression PHPUnit Suite
```bash
php -d memory_limit=512M vendor/phpunit/phpunit/phpunit
```
- **Total Tests Passed:** 218 / 218 (100%)
- **Total Assertions:** 1,888
- **Failures / Errors:** 0
- **Regression Status:** Completely Clean (All previous W10D1-W10D5 and W11D1-W11D3 test suites passed without disruption).
