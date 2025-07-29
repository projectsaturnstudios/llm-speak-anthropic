<?php

namespace LLMSpeak\Anthropic\Enums;

enum ClaudeRole: string
{
    case USER = 'user';
    case ASSISTANT = 'assistant';
}
