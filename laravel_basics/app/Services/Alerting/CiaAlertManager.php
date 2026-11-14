<?php

namespace App\Services\Alerting;

use App\Services\Logging\CiaLogger;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CiaAlertManager
{
    public const ALERT_AI_DUAL_FAILURE = 'AI_DUAL_FAILURE';
    public const ALERT_AI_CONSECUTIVE_FAILURES = 'AI_CONSECUTIVE_FAILURES';
    public const ALERT_CHROMADB_UNAVAILABLE = 'CHROMADB_UNAVAILABLE';
    public const ALERT_HIGH_ERROR_RATE = 'HIGH_ERROR_RATE';
    public const ALERT_CRITICAL_EXCEPTION = 'CRITICAL_EXCEPTION';

    protected const CACHE_KEY_ALERTS = 'cia_recent_alerts';
    protected const THROTTLE_KEY_PREFIX = 'cia_alert_throttle:';

    public function __construct(
        protected CiaLogger $logger
    ) {}

    /**
     * Dispatch an alert if enabled and not throttled by deduplication window.
     *
     * @param string $alertType
     * @param string $summary
     * @param array $details
     * @param bool $force
     * @return bool True if alert was dispatched; false if suppressed by deduplication.
     */
    public function trigger(string $alertType, string $summary, array $details = [], bool $force = false): bool
    {
        if (!config('cia.alerting.enabled', true)) {
            return false;
        }

        $throttleKey = self::THROTTLE_KEY_PREFIX . md5($alertType . ($details['target'] ?? ''));
        $throttleSeconds = (int) config('cia.alerting.throttle_seconds', 300);

        if (!$force && Cache::has($throttleKey)) {
            $this->logger->debug('alerting', "Alert {$alertType} suppressed by deduplication throttle window.", [
                'alert_type' => $alertType,
                'throttle_seconds' => $throttleSeconds,
            ]);
            return false;
        }

        // Set throttle lock in Cache
        Cache::put($throttleKey, now()->toIso8601String(), $throttleSeconds);

        $correlationId = request()?->attributes->get('correlation_id');
        $payload = [
            'alert_type' => $alertType,
            'severity' => 'CRITICAL',
            'summary' => $this->logger->redact($summary),
            'timestamp' => now()->toIso8601String(),
            'correlation_id' => $correlationId,
            'details' => $this->logger->sanitizeContext($details),
        ];

        // 1. Emit structured critical log to configured alert channel
        $channel = config('cia.alerting.channel', 'single');
        Log::channel($channel)->critical("[CIA_ALERT:{$alertType}] {$payload['summary']}", $payload);

        // 2. Persist to recent alerts history in Cache
        $this->recordRecentAlert($payload);

        // 3. Optional webhook notification if configured
        $webhookUrl = config('cia.alerting.webhook_url');
        if (!empty($webhookUrl)) {
            try {
                Http::timeout(3)->post($webhookUrl, $payload);
            } catch (\Throwable $e) {
                Log::warning("Failed to dispatch alert webhook to {$webhookUrl}: " . $e->getMessage());
            }
        }

        return true;
    }

    /**
     * Check if consecutive AI failures warrant an alert.
     */
    public function checkAiFailureAlert(string $provider, int $consecutiveFailures): bool
    {
        $threshold = (int) config('cia.alerting.thresholds.consecutive_ai_failures', 3);
        if ($consecutiveFailures >= $threshold) {
            return $this->trigger(
                self::ALERT_AI_CONSECUTIVE_FAILURES,
                "AI provider '{$provider}' failed {$consecutiveFailures} consecutive times.",
                [
                    'provider' => $provider,
                    'consecutive_failures' => $consecutiveFailures,
                    'threshold' => $threshold,
                    'target' => $provider,
                ]
            );
        }
        return false;
    }

    /**
     * Check if consecutive ChromaDB failures warrant an alert.
     */
    public function checkChromaFailureAlert(int $consecutiveFailures): bool
    {
        $threshold = (int) config('cia.alerting.thresholds.consecutive_chroma_failures', 3);
        if ($consecutiveFailures >= $threshold) {
            return $this->trigger(
                self::ALERT_CHROMADB_UNAVAILABLE,
                "ChromaDB vector store failed {$consecutiveFailures} consecutive times. Local fallback active.",
                [
                    'consecutive_failures' => $consecutiveFailures,
                    'threshold' => $threshold,
                    'target' => 'chromadb',
                ]
            );
        }
        return false;
    }

    /**
     * Check if overall endpoint error rate warrants an alert.
     */
    public function checkErrorRateAlert(float $errorRatePercent, int $totalRequests): bool
    {
        $threshold = (float) config('cia.alerting.thresholds.error_rate_percent', 25.0);
        // Only trigger if at least 10 requests have been recorded to avoid small sample noise
        if ($totalRequests >= 10 && $errorRatePercent >= $threshold) {
            return $this->trigger(
                self::ALERT_HIGH_ERROR_RATE,
                "CIA endpoint error rate reached {$errorRatePercent}%, exceeding threshold of {$threshold}%.",
                [
                    'error_rate_percent' => $errorRatePercent,
                    'total_requests' => $totalRequests,
                    'threshold' => $threshold,
                    'target' => 'error_rate',
                ]
            );
        }
        return false;
    }

    /**
     * Retrieve the recent alerts history.
     */
    public function getRecentAlerts(): array
    {
        return Cache::get(self::CACHE_KEY_ALERTS, []);
    }

    /**
     * Record an alert into Cache history.
     */
    protected function recordRecentAlert(array $alert): void
    {
        $alerts = Cache::get(self::CACHE_KEY_ALERTS, []);
        array_unshift($alerts, $alert);
        if (count($alerts) > 20) {
            array_pop($alerts);
        }
        Cache::put(self::CACHE_KEY_ALERTS, $alerts, 86400); // 24 hours
    }

    /**
     * Reset alerts history and throttles (primarily for automated testing).
     */
    public function clearThrottles(): void
    {
        Cache::forget(self::CACHE_KEY_ALERTS);
    }
}
