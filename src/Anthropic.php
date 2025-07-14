<?php

namespace LLMSpeak\Anthropic;

use LLMSpeak\Anthropic\Repositories\ClaudeMessagesAPIRepository;

class Anthropic
{
    public function __construct(protected array $config)
    {

    }

    public function messages(): ClaudeMessagesAPIRepository
    {
        return new ClaudeMessagesAPIRepository;
    }
    /*
    public function models(): ClaudeModelsAPIRepository
    {

    }

    public function message_batches(): ClaudeMessageBatchesAPIRepository
    {

    }

    public function files(): ClaudeFilesAPIRepository
    {

    }

    public function admin(): ClaudeAdminAPIRepository
    {

    }

    public function experimental(): ClaudeExperimentalAPIRepository
    {

    }
    */
    public function api_url(): string
    {
        return $this->config['api_url'] ?? 'https://api.anthropic.com/v1/';
    }

    public function api_key(): string
    {
        return $this->config['api_key'];
    }

    public function anthropic_version(): string
    {
        return $this->config['extra_headers']['anthropic-version'] ?? '2023-06-01';
    }

    public static function boot(): void
    {
        app()->singleton('anthropic', function () {
            $results = new static(config('llms.services.anthropic'));

            return $results;
        });
    }
}
