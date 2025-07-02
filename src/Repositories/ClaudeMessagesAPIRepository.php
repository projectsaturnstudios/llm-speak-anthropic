<?php

namespace LLMSpeak\Anthropic\Repositories;

use LLMSpeak\Anthropic\Actions\AnthropicAPI\Messages\MessagesEndpoint;
use LLMSpeak\Anthropic\Support\Facades\Claude;

class ClaudeMessagesAPIRepository extends ClaudeAPIRepository
{
    protected ?string $model = null;
    protected ?int $max_tokens = null;
    protected ?array $messages = null;
    protected ?array $tools = null;
    protected ?array $system_prompt = null;
    protected ?float $temperature = null;

    public function withModel(string $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function withMaxTokens(int $tokens): static
    {
        $this->max_tokens = $tokens;
        return $this;
    }

    public function withMessages(array $conversation): MessagesEndpoint
    {
        $this->messages = $conversation;
        return new MessagesEndpoint(
            url: Claude::api_url(),
            api_key: $this->api_key,
            anthropic_version: $this->anthropic_version,
            model: $this->model,
            max_tokens: $this->max_tokens,
            messages: $this->messages,
            system_prompt: $this->system_prompt,
            tools: $this->tools,
            temperature: $this->temperature
        );
    }

    public function withSystemPrompt(array $prompt): static
    {
        $this->system_prompt = $prompt;
        return $this;
    }

    public function withTools(array $tools): static
    {
        $this->tools = $tools;
        return $this;
    }

    public function withTemperature(float $temperature): static
    {
        $this->temperature = $temperature;
        return $this;
    }
}
