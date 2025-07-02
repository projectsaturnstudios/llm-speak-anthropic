<?php

namespace LLMSpeak\Anthropic\Actions\Sagas\Messages\MessagesEndpoint;

use LLMSpeak\Anthropic\ClaudeCallResult;
use ProjectSaturnStudios\PocketFlow\Node;

class PrepareMessagesResultNode extends Node
{
    public function prep(mixed &$shared): mixed
    {
        return $shared['model_response'];
    }

    public function exec(mixed $prep_res): mixed
    {
        return ClaudeCallResult::from($prep_res);
    }
    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {
        $shared = $exec_res;
        return 'finished';
    }
}
