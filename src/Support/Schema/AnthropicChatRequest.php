<?php

namespace LLMSpeak\Anthropic\Support\Schema;

use LLMSpeak\Anthropic\AnthropicRequest;
use Spatie\LaravelData\Data;

class AnthropicChatRequest extends AnthropicRequest
{
    public function __construct(
        string $model
    )
    {
        parent::__construct($model);
    }
}
