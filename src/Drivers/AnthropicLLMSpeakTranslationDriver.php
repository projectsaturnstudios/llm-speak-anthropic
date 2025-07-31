<?php

namespace LLMSpeak\Anthropic\Drivers;

use LLMSpeak\Anthropic\ClaudeMessageRequest;
use LLMSpeak\Anthropic\ClaudeMessageResponse;
use LLMSpeak\Core\Drivers\LLMTranslationDriver;
use LLMSpeak\Core\Support\Requests\LLMSpeakChatRequest;
use LLMSpeak\Core\Support\Responses\LLMSpeakChatResponse;

class AnthropicLLMSpeakTranslationDriver extends LLMTranslationDriver
{
    public function convertRequest(LLMSpeakChatRequest $communique): ClaudeMessageRequest
    {
        // Convert Conversation to messages array
        $messages = [];
        if ($communique->messages) {
            foreach ($communique->messages->getEntries() as $entry) {
                if ($entry instanceof \LLMSpeak\Core\Support\Schema\Conversation\ChatMessage) {
                    $role = match ($entry->role->value) {
                        'model' => 'assistant',  // Map model to assistant for Anthropic
                        'user' => 'user',
                        'system' => 'user',  // System messages should be handled separately, but fallback to user
                        default => $entry->role->value
                    };
                    
                    $messages[] = [
                        'role' => $role,
                        'content' => $entry->content
                    ];
                }
            }
        }

        // Convert SystemInstructions to system string
        $system = null;
        if ($communique->system_instructions) {
            $systemEntries = $communique->system_instructions->getEntries();
            if ($systemEntries->isNotEmpty()) {
                // Combine all system instruction texts into a single string
                $system = $systemEntries->map(function ($instruction) {
                    return $instruction->content; // Access the content property from LLMConversationSchema
                })->implode("\n\n");
            }
        }

        // Convert ToolKit to tools array
        $tools = null;
        if ($communique->tools) {
            $tools = [];
            foreach ($communique->tools->getTools() as $toolDefinition) {
                $tools[] = [
                    'name' => $toolDefinition->tool,  // Map 'tool' to 'name'
                    'description' => $toolDefinition->description,
                    'input_schema' => $toolDefinition->inputSchema  // Map 'inputSchema' to 'input_schema'
                ];
            }
        }

        // Convert max_tokens to string (Anthropic expects string)
        $maxTokens = $communique->max_tokens ? (string) $communique->max_tokens : null;

        return new ClaudeMessageRequest(
            model: $communique->model,
            messages: $messages,
            max_tokens: $maxTokens,
            system: $system,
            tools: $tools,
            tool_choice: $communique->tool_choice,
            temperature: $communique->temperature,
            stream: $communique->stream,
            response_format: $communique->response_format,
            parallel_function_calling: $communique->parallel_function_calling,
            top_p: $communique->top_p,
            top_k: $communique->top_k,
            stop_sequences: $communique->stop,  // Map 'stop' to 'stop_sequences'
            thinking: $communique->reasoning  // Map 'reasoning' to 'thinking'
            // Note: frequency_penalty and presence_penalty not supported by Anthropic
        );
    }

    public function convertResponse(LLMSpeakChatResponse $communique): ClaudeMessageResponse
    {
        // Convert LLMSpeak universal format to Anthropic's format
        $content = [];

        // Extract content from choices
        if (!empty($communique->choices)) {
            $firstChoice = $communique->choices[0];
            $messageContent = $firstChoice['message']['content'] ?? $firstChoice['content'] ?? '';

            $content[] = [
                'type' => 'text',
                'text' => $messageContent
            ];
        }

        // Map finish reason back to Anthropic's stop_reason
        $stopReason = $this->mapToAnthropicStopReason($communique->getFinishReason());

        // Map usage back to Anthropic format
        $usage = [
            'input_tokens' => $communique->getPromptTokens() ?? 0,
            'output_tokens' => $communique->getCompletionTokens() ?? 0
        ];

        return new ClaudeMessageResponse(
            id: $communique->id,
            type: $communique->metadata['anthropic_type'] ?? 'message',
            role: 'assistant',
            content: $content,
            model: $communique->model,
            stop_reason: $stopReason,
            stop_sequence: $communique->metadata['stop_sequence'] ?? null,
            usage: $usage,
            container: $communique->metadata['container'] ?? null
        );
    }

    public function translateRequest(mixed $communique): LLMSpeakChatRequest
    {
        if(!$communique instanceof ClaudeMessageRequest) throw new \InvalidArgumentException('Expected ClaudeMessageRequest instance.');
        
        // Convert Anthropic messages back to Conversation
        $conversation = null;
        if (!empty($communique->messages)) {
            $chatMessages = [];
            foreach ($communique->messages as $message) {
                $role = \LLMSpeak\Core\Enums\ChatRole::from($message['role']);
                $content = $message['content'];
                
                // Handle different content types (string vs array)
                if (is_array($content)) {
                    $textContent = '';
                    foreach ($content as $block) {
                        if (isset($block['type']) && $block['type'] === 'text') {
                            $textContent .= $block['text'] ?? '';
                        }
                    }
                    $content = $textContent;
                }
                
                $chatMessages[] = new \LLMSpeak\Core\Support\Schema\Conversation\ChatMessage($role, $content);
            }
            $conversation = new \LLMSpeak\Core\Support\Schema\Conversation\Conversation($chatMessages);
        }

        // Convert system back to SystemInstructions
        $systemInstructions = null;
        if ($communique->system) {
            $systemInstruction = new \LLMSpeak\Core\Support\Schema\SystemInstructions\SystemInstruction($communique->system);
            $systemInstructions = new \LLMSpeak\Core\Support\Schema\SystemInstructions\SystemInstructions([$systemInstruction]);
        }

        // Convert tools back to ToolKit
        $tools = null;
        if ($communique->tools) {
            $toolDefinitions = [];
            foreach ($communique->tools as $tool) {
                $toolDefinitions[] = new \LLMSpeak\Core\Support\Schema\Tools\ToolDefinition(
                    $tool['name'] ?? '',
                    $tool['description'] ?? '',
                    $tool['input_schema'] ?? []
                );
            }
            $tools = new \LLMSpeak\Core\Support\Schema\Tools\ToolKit($toolDefinitions);
        }

        // Convert max_tokens back to int (Anthropic uses string)
        $maxTokens = is_string($communique->max_tokens) ? (int) $communique->max_tokens : $communique->max_tokens;

        return new \LLMSpeak\Core\Support\Requests\LLMSpeakChatRequest(
            model: $communique->model,
            messages: $conversation,
            tools: $tools,
            system_instructions: $systemInstructions,
            max_tokens: $maxTokens,
            temperature: $communique->temperature,
            tool_choice: $communique->tool_choice,
            response_format: $communique->response_format,
            stream: $communique->stream,
            parallel_function_calling: $communique->parallel_function_calling,
            top_p: $communique->top_p,
            top_k: $communique->top_k,
            frequency_penalty: null,  // Anthropic doesn't support these
            presence_penalty: null,   // Anthropic doesn't support these
            stop: $communique->stop_sequences,  // Map 'stop_sequences' back to 'stop'
            reasoning: $communique->thinking     // Map 'thinking' back to 'reasoning'
        );
    }

    public function translateResponse(mixed $communique): LLMSpeakChatResponse
    {
        if(!$communique instanceof ClaudeMessageResponse) throw new \InvalidArgumentException('Expected ClaudeMessageResponse instance.');
        
        // Map Anthropic response to universal format
        $choices = [];
        if (!empty($communique->content)) {
            $messageContent = '';
            foreach ($communique->content as $contentBlock) {
                if (isset($contentBlock['text'])) {
                    $messageContent .= $contentBlock['text'];
                }
            }
            
            $choices[] = [
                'index' => 0,
                'message' => [
                    'role' => $communique->role ?? 'assistant',
                    'content' => $messageContent
                ],
                'finish_reason' => $this->mapFromAnthropicStopReason($communique->stop_reason)
            ];
        }

        // Map usage information
        $usage = [
            'prompt_tokens' => $communique->usage['input_tokens'] ?? 0,
            'completion_tokens' => $communique->usage['output_tokens'] ?? 0,
            'total_tokens' => ($communique->usage['input_tokens'] ?? 0) + ($communique->usage['output_tokens'] ?? 0)
        ];

        return new \LLMSpeak\Core\Support\Responses\LLMSpeakChatResponse(
            id: $communique->id,
            model: $communique->model,
            created: time(), // Anthropic doesn't provide created timestamp
            choices: $choices,
            usage: $usage,
            finish_reason: $this->mapFromAnthropicStopReason($communique->stop_reason),
            object: 'chat.completion',
            system_fingerprint: null,
            metadata: [
                'anthropic_type' => $communique->type,
                'stop_sequence' => $communique->stop_sequence
            ]
        );
    }

    private function mapFromAnthropicStopReason(?string $stopReason): ?string
    {
        return match ($stopReason) {
            'end_turn' => 'stop',
            'max_tokens' => 'length',
            'stop_sequence' => 'stop',
            'tool_use' => 'tool_calls',
            default => $stopReason
        };
    }

    /**
     * Map universal finish_reason back to Anthropic's stop_reason
     */
    private function mapToAnthropicStopReason(?string $finishReason): ?string
    {
        return match($finishReason) {
            'stop' => 'end_turn',
            'length' => 'max_tokens',
            'tool_calls' => 'tool_use',
            default => 'end_turn'
        };
    }
}
