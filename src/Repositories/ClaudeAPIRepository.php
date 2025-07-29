<?php

namespace LLMSpeak\Anthropic\Repositories;

abstract class ClaudeAPIRepository
{
    protected ?string $api_key = null;
    protected ?string $anthropic_version = null;

    public function withApikey(string $api_key): static
    {
        $this->api_key = $api_key;

        return $this;
    }

    public function withAnthropicVersion(string $anthropic_version): static
    {
        $this->anthropic_version = $anthropic_version;

        return $this;
    }
}
