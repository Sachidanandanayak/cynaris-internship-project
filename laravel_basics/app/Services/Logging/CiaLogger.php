<?php

namespace App\Services\Logging;

use Illuminate\Support\Facades\Log;

class CiaLogger
{
    /**
     * Log an informational CIA event.
     */
    public function info(string $operation, string $message, array $context = []): void
    {
        $this->write('info', $operation, $message, $context);
    }

    /**
     * Log a debug CIA event.
     */
    public function debug(string $operation, string $message, array $context = []): void
    {
        $this->write('debug', $operation, $message, $context);
    }

    /**
     * Log a warning CIA event.
     */
    public function warning(string $operation, string $message, array $context = []): void
    {
        $this->write('warning', $operation, $message, $context);
    }

    /**
     * Log an error CIA event.
     */
    public function error(string $operation, string $message, array $context = []): void
    {
        $this->write('error', $operation, $message, $context);
    }

    /**
     * Dispatch structured log entry through Monolog.
     */
    protected function write(string $level, string $operation, string $message, array $context = []): void
    {
        $channel = config('cia.logging.channel', 'single');
        $correlationId = request()?->attributes->get('correlation_id');

        $structured = array_merge([
            'cia_operation' => $operation,
            'correlation_id' => $correlationId,
            'timestamp' => now()->toIso8601String(),
        ], $this->sanitizeContext($context));

        $sanitizedMessage = $this->redact($message);

        Log::channel($channel)->{$level}("[CIA:{$operation}] {$sanitizedMessage}", $structured);
    }

    /**
     * Redact API keys, tokens, emails, phone numbers, and secrets from text.
     */
    public function redact(string $text): string
    {
        // 1. Redact API keys & Bearer tokens
        $text = preg_replace('/Bearer\s+[a-zA-Z0-9_\-\.]+/i', 'Bearer [REDACTED]', $text);
        $text = preg_replace('/gsk_[a-zA-Z0-9]+/i', 'gsk_[REDACTED]', $text);
        $text = preg_replace('/sk-or-[a-zA-Z0-9_\-]+/i', 'sk-or-[REDACTED]', $text);

        // 2. Redact PII (Emails & Phones) if enabled
        if (config('cia.logging.redact_pii', true)) {
            // Email address pattern
            $text = preg_replace('/[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+/i', '[REDACTED_EMAIL]', $text);
            // Phone number pattern (e.g., +1-555-123-4567, 9876543210, (555) 123-4567)
            $text = preg_replace('/(?:\+?\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}/', '[REDACTED_PHONE]', $text);
        }

        return $text;
    }

    /**
     * Recursively sanitize context arrays: scrub sensitive keys, redact PII, truncate long strings.
     */
    public function sanitizeContext(array $context): array
    {
        $sensitiveKeys = ['password', 'secret', 'token', 'key', 'authorization', 'cookie', 'resume_content', 'pdf_content', 'api_key'];
        $maxLen = (int) config('cia.logging.max_payload_length', 300);

        $sanitized = [];
        foreach ($context as $key => $value) {
            $lowerKey = strtolower((string) $key);

            // Redact known sensitive keys completely
            $isSensitive = false;
            foreach ($sensitiveKeys as $sensitiveWord) {
                if ($lowerKey === $sensitiveWord || str_contains($lowerKey, $sensitiveWord)) {
                    $isSensitive = true;
                    break;
                }
            }
            if ($isSensitive) {
                $sanitized[$key] = '[REDACTED]';
                continue;
            }

            // Redact resume text or extracted body (never log full candidate resumes)
            if (in_array($lowerKey, ['resume', 'extracted_text', 'resume_text', 'document_body'], true)) {
                if (is_string($value)) {
                    $sanitized[$key] = sprintf(
                        '[REDACTED_RESUME_TEXT: length=%d, sha256=%s]',
                        strlen($value),
                        substr(hash('sha256', $value), 0, 12)
                    );
                } else {
                    $sanitized[$key] = '[REDACTED_RESUME_FILE]';
                }
                continue;
            }

            if (is_string($value)) {
                $redacted = $this->redact($value);
                if (strlen($redacted) > $maxLen) {
                    $redacted = substr($redacted, 0, $maxLen) . '... [TRUNCATED]';
                }
                $sanitized[$key] = $redacted;
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeContext($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
