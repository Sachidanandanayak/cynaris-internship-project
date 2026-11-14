<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CIA Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Structured logging options for CIA Career Intelligence modules.
    | Controls log channels, PII redaction, and maximum logged payload sizes.
    |
    */

    'logging' => [
        'enabled' => env('CIA_LOGGING_ENABLED', true),
        'channel' => env('CIA_LOG_CHANNEL', 'single'),
        'redact_pii' => env('CIA_LOG_REDACT_PII', true),
        'max_payload_length' => (int) env('CIA_LOG_MAX_PAYLOAD', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | CIA Monitoring & Telemetry Configuration
    |--------------------------------------------------------------------------
    |
    | Controls real-time tracking of request latencies, error rates,
    | AI provider health, and ChromaDB vector store reliability.
    |
    */

    'monitoring' => [
        'enabled' => env('CIA_MONITORING_ENABLED', true),
        'window_minutes' => (int) env('CIA_METRICS_WINDOW_MINUTES', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | CIA Alerting Configuration
    |--------------------------------------------------------------------------
    |
    | Production-ready thresholds and alert deduplication parameters for
    | critical failure scenarios (dual AI provider outage, repeated 5xx errors).
    |
    */

    'alerting' => [
        'enabled' => env('CIA_ALERTING_ENABLED', true),
        'channel' => env('CIA_ALERT_CHANNEL', 'single'),
        'throttle_seconds' => (int) env('CIA_ALERT_THROTTLE_SECONDS', 300), // 5 minute deduplication
        'thresholds' => [
            'consecutive_ai_failures' => (int) env('CIA_ALERT_AI_FAILURES_THRESHOLD', 3),
            'consecutive_chroma_failures' => (int) env('CIA_ALERT_CHROMA_FAILURES_THRESHOLD', 3),
            'error_rate_percent' => (float) env('CIA_ALERT_ERROR_RATE_THRESHOLD', 25.0),
            'latency_p95_ms' => (float) env('CIA_ALERT_LATENCY_P95_THRESHOLD', 5000.0),
        ],
        'webhook_url' => env('CIA_ALERT_WEBHOOK_URL', null),
    ],

];
