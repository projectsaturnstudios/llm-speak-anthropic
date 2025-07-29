<?php

namespace LLMSpeak\Anthropic;

use Spatie\LaravelData\Data;

/**
 * ClaudeMessageResponse - Anthropic API Response Handler
 * 
 * Represents the complete response structure from the Anthropic Messages API.
 * Built using the same pattern as ClaudeMessageRequest for consistency.
 * 
 * Usage Examples:
 * 
 * // Create from API response
 * $response = ClaudeMessageResponse::fromApiResponse($apiData);
 * 
 * // Access response data
 * $content = $response->getTextContent();
 * $tokens = $response->getTotalTokens();
 * $wasToolUsed = $response->usedTools();
 * 
 * // Check completion status
 * if ($response->completedNaturally()) {
 *     // Handle successful completion
 * }
 */
class ClaudeMessageResponse extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $role,
        public readonly array $content,
        public readonly string $model,
        public readonly string|null $stop_reason,
        public readonly string|null $stop_sequence,
        public readonly array $usage,
        public readonly array|null $container = null
    ) {}

    /**
     * Create instance from raw API response array
     */
    public static function fromApiResponse(array $response): self
    {
        return new self(
            id: $response['id'],
            type: $response['type'] ?? 'message',
            role: $response['role'] ?? 'assistant',
            content: $response['content'] ?? [],
            model: $response['model'],
            stop_reason: $response['stop_reason'] ?? null,
            stop_sequence: $response['stop_sequence'] ?? null,
            usage: $response['usage'] ?? [],
            container: $response['container'] ?? null
        );
    }

    // Content Access Methods
    
    public function getTextContent(): string|null
    {
        foreach ($this->content as $block) {
            if ($block['type'] === 'text' && isset($block['text'])) {
                return $block['text'];
            }
        }
        return null;
    }

    public function getAllTextContent(): array
    {
        $textBlocks = [];
        foreach ($this->content as $block) {
            if ($block['type'] === 'text' && isset($block['text'])) {
                $textBlocks[] = $block['text'];
            }
        }
        return $textBlocks;
    }

    public function getToolUseBlocks(): array
    {
        $toolBlocks = [];
        foreach ($this->content as $block) {
            if ($block['type'] === 'tool_use') {
                $toolBlocks[] = $block;
            }
        }
        return $toolBlocks;
    }

    public function getThinkingContent(): array
    {
        $thinkingBlocks = [];
        foreach ($this->content as $block) {
            if ($block['type'] === 'thinking' && isset($block['content'])) {
                $thinkingBlocks[] = $block['content'];
            }
        }
        return $thinkingBlocks;
    }

    // Status Check Methods
    
    public function wasStoppedByTokenLimit(): bool
    {
        return $this->stop_reason === 'max_tokens';
    }

    public function completedNaturally(): bool
    {
        return $this->stop_reason === 'end_turn';
    }

    public function wasStoppedBySequence(): bool
    {
        return $this->stop_reason === 'stop_sequence';
    }

    public function usedTools(): bool
    {
        return $this->stop_reason === 'tool_use' || !empty($this->getToolUseBlocks());
    }

    // Token Analysis Methods
    
    public function getTotalTokens(): int
    {
        return $this->getInputTokens() + $this->getOutputTokens();
    }

    public function getInputTokens(): int
    {
        return ($this->usage['input_tokens'] ?? 0) + 
               ($this->usage['cache_creation_input_tokens'] ?? 0) + 
               ($this->usage['cache_read_input_tokens'] ?? 0);
    }

    public function getOutputTokens(): int
    {
        return $this->usage['output_tokens'] ?? 0;
    }

    public function usedCaching(): bool
    {
        return isset($this->usage['cache_creation_input_tokens']) || 
               isset($this->usage['cache_read_input_tokens']);
    }

    public function getCacheEfficiency(): float
    {
        $totalInput = $this->getInputTokens();
        $cachedTokens = $this->usage['cache_read_input_tokens'] ?? 0;
        return $totalInput > 0 ? ($cachedTokens / $totalInput) * 100 : 0.0;
    }
}
