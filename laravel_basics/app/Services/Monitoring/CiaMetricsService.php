<?php

namespace App\Services\Monitoring;

use Illuminate\Support\Facades\Cache;

class CiaMetricsService
{
    protected const CACHE_KEY_METRICS = 'cia_metrics_data';

    /**
     * In-memory cache for fast local access during a single lifecycle.
     */
    protected static ?array $inMemoryState = null;

    /**
     * Record an incoming HTTP request execution.
     */
    public function recordRequest(string $endpoint, int $statusCode, float $durationMs): void
    {
        $state = $this->loadState();

        $state['requests']['total']++;

        if ($statusCode >= 200 && $statusCode < 400) {
            $state['requests']['successful']++;
        } elseif ($statusCode >= 400 && $statusCode < 500) {
            $state['requests']['client_errors']++;
        } else {
            $state['requests']['server_errors']++;
        }

        $totalReqs = $state['requests']['total'];
        $failedReqs = $state['requests']['server_errors'];
        $state['requests']['error_rate_percent'] = $totalReqs > 0
            ? round(($failedReqs / $totalReqs) * 100.0, 2)
            : 0.0;

        // Record latency
        $state['latency']['samples'][] = $durationMs;
        if (count($state['latency']['samples']) > 100) {
            array_shift($state['latency']['samples']);
        }

        $count = count($state['latency']['samples']);
        $sum = array_sum($state['latency']['samples']);
        $state['latency']['avg_ms'] = $count > 0 ? round($sum / $count, 2) : 0.0;
        $state['latency']['min_ms'] = $count > 0 ? min($state['latency']['samples']) : 0.0;
        $state['latency']['max_ms'] = $count > 0 ? max($state['latency']['samples']) : 0.0;
        $state['latency']['p95_ms'] = $this->calculateP95($state['latency']['samples']);

        // Record endpoint breakdown
        if (!isset($state['endpoints'][$endpoint])) {
            $state['endpoints'][$endpoint] = ['total' => 0, 'errors' => 0];
        }
        $state['endpoints'][$endpoint]['total']++;
        if ($statusCode >= 400) {
            $state['endpoints'][$endpoint]['errors']++;
        }

        $this->saveState($state);
    }

    /**
     * Record an AI provider call event.
     */
    public function recordAiCall(
        string $provider,
        bool $success,
        float $durationMs,
        ?string $error = null,
        bool $isFallback = false
    ): void {
        $state = $this->loadState();
        $p = strtolower($provider);

        if (!isset($state['ai_providers'][$p])) {
            $state['ai_providers'][$p] = [
                'calls' => 0,
                'success' => 0,
                'failures' => 0,
                'timeouts' => 0,
                'consecutive_failures' => 0,
                'status' => 'healthy',
            ];
        }

        $state['ai_providers'][$p]['calls']++;

        if ($success) {
            $state['ai_providers'][$p]['success']++;
            $state['ai_providers'][$p]['consecutive_failures'] = 0;
            $state['ai_providers'][$p]['status'] = 'healthy';
        } else {
            $state['ai_providers'][$p]['failures']++;
            $state['ai_providers'][$p]['consecutive_failures']++;

            if ($error && str_contains(strtolower($error), 'timeout')) {
                $state['ai_providers'][$p]['timeouts']++;
            }

            $threshold = (int) config('cia.alerting.thresholds.consecutive_ai_failures', 3);
            if ($state['ai_providers'][$p]['consecutive_failures'] >= $threshold) {
                $state['ai_providers'][$p]['status'] = 'unavailable';
            } else {
                $state['ai_providers'][$p]['status'] = 'degraded';
            }
        }

        if ($isFallback) {
            $state['ai_providers']['fallback_count']++;
        }

        $this->saveState($state);
    }

    /**
     * Record a dual AI outage event (both Groq and OpenRouter failed).
     */
    public function recordDualAiFailure(): void
    {
        $state = $this->loadState();
        $state['ai_providers']['dual_failure_count']++;
        $state['ai_providers']['fallback_count']++;
        $this->saveState($state);
    }

    /**
     * Record a ChromaDB operation event.
     */
    public function recordChromaCall(
        string $operation,
        bool $success,
        float $durationMs,
        ?string $error = null,
        bool $isFallback = false
    ): void {
        $state = $this->loadState();

        $state['chromadb']['calls']++;

        if ($success) {
            $state['chromadb']['success']++;
            $state['chromadb']['consecutive_failures'] = 0;
            $state['chromadb']['status'] = 'healthy';
        } else {
            $state['chromadb']['failures']++;
            $state['chromadb']['consecutive_failures']++;

            $threshold = (int) config('cia.alerting.thresholds.consecutive_chroma_failures', 3);
            if ($state['chromadb']['consecutive_failures'] >= $threshold) {
                $state['chromadb']['status'] = 'unavailable';
            } else {
                $state['chromadb']['status'] = 'degraded';
            }
        }

        if ($isFallback) {
            $state['chromadb']['fallback_count']++;
        }

        $this->saveState($state);
    }

    /**
     * Record a critical application exception.
     */
    public function recordException(string $operation, \Throwable $e): void
    {
        $state = $this->loadState();

        $state['exceptions']['total']++;
        $state['exceptions']['recent'][] = [
            'timestamp' => now()->toIso8601String(),
            'operation' => $operation,
            'message' => substr($e->getMessage(), 0, 200),
            'class' => get_class($e),
        ];

        if (count($state['exceptions']['recent']) > 10) {
            array_shift($state['exceptions']['recent']);
        }

        $this->saveState($state);
    }

    /**
     * Retrieve the complete system health and monitoring metrics snapshot.
     */
    public function getHealthMetrics(): array
    {
        $state = $this->loadState();

        // Calculate overarching system status
        $systemStatus = 'healthy';
        $groqStatus = $state['ai_providers']['groq']['status'] ?? 'healthy';
        $openRouterStatus = $state['ai_providers']['openrouter']['status'] ?? 'healthy';
        $chromaStatus = $state['chromadb']['status'] ?? 'healthy';
        $errorRate = $state['requests']['error_rate_percent'] ?? 0.0;

        if ($groqStatus === 'unavailable' && $openRouterStatus === 'unavailable') {
            $systemStatus = 'critical';
        } elseif ($chromaStatus === 'unavailable' || $errorRate >= 25.0) {
            $systemStatus = 'critical';
        } elseif ($groqStatus === 'degraded' || $openRouterStatus === 'degraded' || $chromaStatus === 'degraded' || $errorRate >= 5.0) {
            $systemStatus = 'degraded';
        }

        return [
            'status' => $systemStatus,
            'timestamp' => now()->toIso8601String(),
            'requests' => [
                'total' => $state['requests']['total'],
                'successful' => $state['requests']['successful'],
                'client_errors' => $state['requests']['client_errors'],
                'server_errors' => $state['requests']['server_errors'],
                'error_rate_percent' => $state['requests']['error_rate_percent'],
            ],
            'latency' => [
                'avg_ms' => $state['latency']['avg_ms'],
                'min_ms' => $state['latency']['min_ms'],
                'max_ms' => $state['latency']['max_ms'],
                'p95_ms' => $state['latency']['p95_ms'],
            ],
            'ai_providers' => [
                'groq' => $state['ai_providers']['groq'],
                'openrouter' => $state['ai_providers']['openrouter'],
                'fallback_count' => $state['ai_providers']['fallback_count'],
                'dual_failure_count' => $state['ai_providers']['dual_failure_count'],
            ],
            'chromadb' => $state['chromadb'],
            'exceptions' => [
                'total' => $state['exceptions']['total'],
                'recent' => $state['exceptions']['recent'],
            ],
            'endpoints' => $state['endpoints'],
        ];
    }

    /**
     * Reset metrics state (primarily for automated testing).
     */
    public function reset(): void
    {
        self::$inMemoryState = $this->getInitialState();
        Cache::forget(self::CACHE_KEY_METRICS);
    }

    /**
     * Compute 95th percentile latency from samples.
     *
     * @param array<int, float> $samples
     */
    protected function calculateP95(array $samples): float
    {
        if (empty($samples)) {
            return 0.0;
        }

        sort($samples);
        $count = count($samples);
        $index = (int) ceil(0.95 * $count) - 1;
        $index = max(0, min($index, $count - 1));

        return round((float) $samples[$index], 2);
    }

    /**
     * Load current telemetry state from in-memory or Cache.
     */
    protected function loadState(): array
    {
        if (self::$inMemoryState !== null) {
            return self::$inMemoryState;
        }

        $cached = Cache::get(self::CACHE_KEY_METRICS);
        if (is_array($cached)) {
            self::$inMemoryState = $cached;
            return self::$inMemoryState;
        }

        self::$inMemoryState = $this->getInitialState();
        return self::$inMemoryState;
    }

    /**
     * Persist telemetry state.
     */
    protected function saveState(array $state): void
    {
        self::$inMemoryState = $state;
        // Keep in Cache for 2 hours
        Cache::put(self::CACHE_KEY_METRICS, $state, 7200);
    }

    /**
     * Construct the baseline initial metrics state.
     */
    protected function getInitialState(): array
    {
        return [
            'requests' => [
                'total' => 0,
                'successful' => 0,
                'client_errors' => 0,
                'server_errors' => 0,
                'error_rate_percent' => 0.0,
            ],
            'latency' => [
                'samples' => [],
                'avg_ms' => 0.0,
                'min_ms' => 0.0,
                'max_ms' => 0.0,
                'p95_ms' => 0.0,
            ],
            'ai_providers' => [
                'groq' => [
                    'calls' => 0,
                    'success' => 0,
                    'failures' => 0,
                    'timeouts' => 0,
                    'consecutive_failures' => 0,
                    'status' => 'healthy',
                ],
                'openrouter' => [
                    'calls' => 0,
                    'success' => 0,
                    'failures' => 0,
                    'timeouts' => 0,
                    'consecutive_failures' => 0,
                    'status' => 'healthy',
                ],
                'fallback_count' => 0,
                'dual_failure_count' => 0,
            ],
            'chromadb' => [
                'calls' => 0,
                'success' => 0,
                'failures' => 0,
                'consecutive_failures' => 0,
                'fallback_count' => 0,
                'status' => 'healthy',
            ],
            'exceptions' => [
                'total' => 0,
                'recent' => [],
            ],
            'endpoints' => [],
        ];
    }
}
