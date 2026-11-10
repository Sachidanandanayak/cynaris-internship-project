<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ResilientAiGateway
{
    /**
     * Execute chat completion across a 3-tier resilient provider pipeline:
     * Tier 1: Groq API
     * Tier 2: OpenRouter API
     * Tier 3: Deterministic Fallback Signal
     *
     * @param string $systemPrompt
     * @param string $userPrompt
     * @param array{
     *     temperature?: float,
     *     response_format?: array,
     *     validator?: callable,
     *     groq_engine_name?: string,
     *     openrouter_engine_name?: string,
     *     fallback_engine_name?: string,
     *     groq_model?: string,
     *     openrouter_model?: string,
     *     groq_timeout?: int,
     *     openrouter_timeout?: int,
     *     context?: array
     * } $options
     * @return ResilientAiResponse
     */
    public function executeChat(string $systemPrompt, string $userPrompt, array $options = []): ResilientAiResponse
    {
        $context = $options['context'] ?? [];

        // ----------------------------------------------------
        // Tier 1: Groq AI Primary Provider
        // ----------------------------------------------------
        $groqKey = trim((string) config('services.groq.key'));
        if (!empty($groqKey)) {
            $groqEndpoint = config('services.groq.endpoint') ?: config('services.groq.url') ?: 'https://api.groq.com/openai/v1/chat/completions';
            $groqModel = $options['groq_model'] ?? config('services.groq.model', 'llama-3.3-70b-versatile');
            $groqTimeout = (int) ($options['groq_timeout'] ?? config('services.groq.timeout', 10));

            try {
                $result = $this->queryProvider(
                    provider: 'Groq',
                    endpoint: $groqEndpoint,
                    apiKey: $groqKey,
                    model: $groqModel,
                    timeout: $groqTimeout,
                    systemPrompt: $systemPrompt,
                    userPrompt: $userPrompt,
                    options: $options
                );

                if ($result !== null) {
                    $engineName = $options['groq_engine_name'] ?? 'groq_ai';
                    return ResilientAiResponse::success(
                        data: $result['data'],
                        rawContent: $result['raw'],
                        provider: 'groq',
                        engine: $engineName,
                        metadata: ['model' => $groqModel, 'provider' => 'groq']
                    );
                }
            } catch (\Throwable $e) {
                $this->logWarning('Groq API error, attempting OpenRouter fallback: ' . $e->getMessage(), $context);
            }
        } else {
            $this->logInfo('Groq API key not configured, skipping to OpenRouter fallback.', $context);
        }

        // ----------------------------------------------------
        // Tier 2: OpenRouter Secondary Provider Fallback
        // ----------------------------------------------------
        $openRouterKey = trim((string) config('services.openrouter.key'));
        if (!empty($openRouterKey)) {
            $openRouterEndpoint = config('services.openrouter.endpoint') ?: config('services.openrouter.url') ?: 'https://openrouter.ai/api/v1/chat/completions';
            $openRouterModel = $options['openrouter_model'] ?? config('services.openrouter.model', 'meta-llama/llama-3.3-70b-instruct');
            $openRouterTimeout = (int) ($options['openrouter_timeout'] ?? config('services.openrouter.timeout', 15));

            try {
                $result = $this->queryProvider(
                    provider: 'OpenRouter',
                    endpoint: $openRouterEndpoint,
                    apiKey: $openRouterKey,
                    model: $openRouterModel,
                    timeout: $openRouterTimeout,
                    systemPrompt: $systemPrompt,
                    userPrompt: $userPrompt,
                    options: $options
                );

                if ($result !== null) {
                    $engineName = $options['openrouter_engine_name'] ?? 'openrouter_llama_3_3_70b';
                    return ResilientAiResponse::success(
                        data: $result['data'],
                        rawContent: $result['raw'],
                        provider: 'openrouter',
                        engine: $engineName,
                        metadata: ['model' => $openRouterModel, 'provider' => 'openrouter']
                    );
                }
            } catch (\Throwable $e) {
                $this->logWarning('OpenRouter fallback error, engaging deterministic engine: ' . $e->getMessage(), $context);
            }
        } else {
            $this->logInfo('OpenRouter API key not configured, engaging deterministic fallback.', $context);
        }

        // ----------------------------------------------------
        // Tier 3: Deterministic Fallback Signal
        // ----------------------------------------------------
        $fallbackEngine = $options['fallback_engine_name'] ?? 'deterministic_fallback';
        return ResilientAiResponse::fallback(
            engine: $fallbackEngine,
            errorMessage: 'Both primary and secondary AI providers unavailable or unconfigured.'
        );
    }

    /**
     * Dispatch an HTTP chat request to an AI provider with strict retry/failover policies.
     */
    protected function queryProvider(
        string $provider,
        string $endpoint,
        string $apiKey,
        string $model,
        int $timeout,
        string $systemPrompt,
        string $userPrompt,
        array $options
    ): ?array {
        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => (float) ($options['temperature'] ?? 0.2),
        ];

        if (!empty($options['response_format'])) {
            $payload['response_format'] = $options['response_format'];
        }

        // Retry policy: Catch connection exceptions with 2 retries (100ms backoff).
        // For non-retryable 4xx (401, 403, 422), fail immediately without retry.
        $response = Http::withToken($apiKey)
            ->timeout($timeout)
            ->retry(2, 100, function ($exception) {
                if ($exception instanceof RequestException && in_array($exception->response->status(), [401, 403, 422])) {
                    return false;
                }
                return true;
            }, throw: false)
            ->post($endpoint, $payload);

        if (!$response->successful()) {
            $status = $response->status();
            $this->logWarning("{$provider} API returned HTTP status {$status}", [
                'status' => $status,
                'snippet' => substr($response->body(), 0, 150),
            ]);
            return null;
        }

        $body = $response->json();
        $rawContent = $body['choices'][0]['message']['content'] ?? null;

        if (!is_string($rawContent) || trim($rawContent) === '') {
            $this->logWarning("{$provider} returned empty message content.");
            return null;
        }

        $cleanedJson = $this->stripMarkdownFences($rawContent);
        $decoded = json_decode($cleanedJson, true);

        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            $this->logWarning("{$provider} returned malformed or unparseable JSON.", [
                'json_error' => json_last_error_msg(),
                'snippet' => substr($cleanedJson, 0, 150),
            ]);
            return null;
        }

        // Optional custom validator callback
        if (isset($options['validator']) && is_callable($options['validator'])) {
            if (!$options['validator']($decoded)) {
                $this->logWarning("{$provider} payload failed schema validation.", [
                    'keys' => array_keys($decoded),
                ]);
                return null;
            }
        }

        return [
            'data' => $decoded,
            'raw' => $rawContent,
        ];
    }

    /**
     * Safely strip Markdown code fences (e.g. ```json ... ``` or ``` ... ```).
     */
    public function stripMarkdownFences(string $content): string
    {
        $clean = trim($content);

        // Standard markdown block match
        if (str_starts_with($clean, '```')) {
            $clean = preg_replace('/^```(?:json|javascript|text)?\s*/i', '', $clean);
            $clean = preg_replace('/\s*```$/', '', $clean);
            $clean = trim($clean);
        }

        // Embedded code block extraction if wrapped in outer text
        if (!str_starts_with($clean, '{') && !str_starts_with($clean, '[')) {
            if (preg_match('/```(?:json)?\s*(\{.*\}|\[.*\])\s*```/is', $clean, $matches)) {
                $clean = trim($matches[1]);
            } elseif (preg_match('/(\{.*\}|\[.*\])/is', $clean, $matches)) {
                $clean = trim($matches[1]);
            }
        }

        return $clean;
    }

    /**
     * Redact API keys and bearer tokens from strings to prevent credential exposure.
     */
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

    /**
     * Recursively sanitize context arrays for logging.
     */
    public function sanitizeContext(array $context): array
    {
        $sanitized = [];
        foreach ($context as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = $this->redactSecrets($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeContext($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    protected function logWarning(string $message, array $context = []): void
    {
        Log::warning($this->redactSecrets($message), $this->sanitizeContext($context));
    }

    protected function logInfo(string $message, array $context = []): void
    {
        Log::info($this->redactSecrets($message), $this->sanitizeContext($context));
    }
}
