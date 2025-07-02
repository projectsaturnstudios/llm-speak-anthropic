<?php

namespace LLMSpeak\Anthropic\Support\Facades;

use Illuminate\Support\Facades\Facade;
use LLMSpeak\Anthropic\Repositories\ClaudeMessagesAPIRepository;

/**
 * @method static string api_url()
 * @method static string api_key()
 * @method static string anthropic_version()
 * @method static ClaudeMessagesAPIRepository messages()
 *
 * @see \LLMSpeak\Anthropic\Anthropic
 */
class Claude extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'anthropic';
    }
}
