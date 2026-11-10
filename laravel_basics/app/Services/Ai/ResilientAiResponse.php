<?php

namespace App\Services\Ai;

class ResilientAiResponse
{
    /**
     * @param bool $success Whether an AI provider successfully returned and validated the response
     * @param array<string, mixed>|null $data Parsed JSON payload
     * @param string|null $rawContent Raw message content from AI model
     * @param string $provider 'groq', 'openrouter', or 'none'
     * @param string $engine Engine identifier (e.g. 'groq_ai', 'openrouter_llama_3_3_70b', 'deterministic_fallback')
     * @param string|null $errorMessage Diagnostic error message if failed
     * @param array<string, mixed> $metadata Additional diagnostics (status code, latency, attempts, etc.)
     */
    public function __construct(
        protected bool $success,
        protected ?array $data = null,
        protected ?string $rawContent = null,
        protected string $provider = 'none',
        protected string $engine = 'deterministic_fallback',
        protected ?string $errorMessage = null,
        protected array $metadata = []
    ) {}

    /**
     * Create a successful response instance.
     */
    public static function success(
        array $data,
        ?string $rawContent,
        string $provider,
        string $engine,
        array $metadata = []
    ): self {
        return new self(
            success: true,
            data: $data,
            rawContent: $rawContent,
            provider: $provider,
            engine: $engine,
            errorMessage: null,
            metadata: $metadata
        );
    }

    /**
     * Create a fallback response instance indicating all AI providers failed or were unconfigured.
     */
    public static function fallback(
        string $engine = 'deterministic_fallback',
        ?string $errorMessage = null,
        array $metadata = []
    ): self {
        return new self(
            success: false,
            data: null,
            rawContent: null,
            provider: 'none',
            engine: $engine,
            errorMessage: $errorMessage,
            metadata: $metadata
        );
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFallback(): bool
    {
        return !$this->success;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function getRawContent(): ?string
    {
        return $this->rawContent;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getEngine(): string
    {
        return $this->engine;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
