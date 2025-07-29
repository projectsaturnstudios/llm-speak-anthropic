<?php

namespace LLMSpeak\Anthropic;

use LLMSpeak\Anthropic\Enums\ClaudeRole;
use Spatie\LaravelData\Data;

class ClaudeCallResult extends Data
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $type = null,
        public readonly ?ClaudeRole $role = null,
        public readonly ?string $model = null,
        public readonly ?array $content = null,
        public readonly ?string $stop_reason = null,
        public readonly ?string $stop_sequence = null,
        public readonly ?array $usage = null,
    ) {}
}
