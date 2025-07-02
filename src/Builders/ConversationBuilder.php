<?php

namespace LLMSpeak\Anthropic\Builders;

use LLMSpeak\Anthropic\Enums\ClaudeRole;

class ConversationBuilder
{
    protected array $conversation = [];

    public function addText(ClaudeRole $role, string $content): static
    {
        $this->conversation[] = [
            'role' => $role->value,
            'content' => $content,
        ];

        return $this;
    }
    public function addToolRequest(string $id, string $name, array $input): static
    {
        $this->conversation[] = [
            'role' => 'assistant',
            'content' => [
                [
                    'type' => 'tool_use',
                    'id' => $id,
                    'name' => $name,
                    'input' => $input
                ]
            ]
        ];

        return $this;
    }
    public function addToolResult(string $tool_use_id, mixed $content): static
    {
        $this->conversation[] = [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'tool_result',
                    'tool_use_id' => $tool_use_id,
                    'content' => $content
                ]
            ]
        ];
        return $this;
    }

    public function addContent(ClaudeRole $role, array $content): static
    {
        return $this;
    }

    public function render(): array
    {
        return $this->conversation;
    }
}
