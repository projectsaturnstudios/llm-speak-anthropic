<?php

namespace LLMSpeak\Anthropic\Actions\AnthropicAPI\Messages;

use Spatie\LaravelData\Data;
use LLMSpeak\Anthropic\ClaudeCallResult;
use LLMSpeak\Anthropic\Enums\ClaudeRole;
use Lorisleiva\Actions\Concerns\AsAction;
use LLMSpeak\Anthropic\Support\Facades\Claude;
use LLMSpeak\Anthropic\Builders\SystemPromptBuilder;
use LLMSpeak\Anthropic\Builders\ConversationBuilder;
use LLMSpeak\Anthropic\Actions\Sagas\Messages\MessagesEndpoint\PrepareMessagesResultNode;
use LLMSpeak\Anthropic\Actions\Sagas\Messages\MessagesEndpoint\ClaudeMessagesEndpointNode;
use LLMSpeak\Anthropic\Actions\Sagas\Messages\MessagesEndpoint\PrepareMessagesRequestNode;

class MessagesEndpoint extends Data
{
    use AsAction;

    protected string $uri = 'messages';

    public function __construct(
        public readonly string $url,
        public readonly string $api_key,
        public readonly string $anthropic_version,
        public readonly string $model,
        public readonly int $max_tokens,
        public readonly array $messages,
        public readonly ?array $system_prompt = null,
        public readonly ?array $tools = null,
        public readonly ?float $temperature = null,
    ) {}

    public function handle(): ClaudeCallResult
    {
        $work_nodes = new PrepareMessagesRequestNode;
        $work_nodes->next(new ClaudeMessagesEndpointNode("{$this->url}{$this->uri}"), 'call')
            ->next(new PrepareMessagesResultNode, 'wrap-up');

        $shared = [
            'available_parameters' => $this->toArray()
        ];

        $results = flow($work_nodes, $shared);
        return $results;
    }

    //public function stream()
    //public function structure()

    public static function test():  ClaudeCallResult
    {
        $convo = (new ConversationBuilder())
            ->addText(ClaudeRole::ASSISTANT, 'Yes?')
            ->addText(ClaudeRole::USER, 'What is the sky blue?')
            ->render();

        $system = (new SystemPromptBuilder())
            ->addText('You are an astrophysicist. You don\'t have time for my small talk')
            ->addText('Keep your answers to less than 20 words')
            ->render();

        return Claude::messages()
            ->withApikey(Claude::api_key())
            ->withAnthropicVersion(Claude::anthropic_version())
            ->withModel("claude-sonnet-4-20250514")
            ->withMaxTokens(500)
            ->withSystemPrompt($system)
            ->withTemperature(0.1125478)
            ->withMessages($convo)
            ->handle();
    }

    public static function test2():  ClaudeCallResult
    {
        $convo = (new ConversationBuilder())
            ->addText(ClaudeRole::ASSISTANT, 'Hi!?')
            ->addText(ClaudeRole::USER, 'Can you shut off the light for me?.')
            ->render();

        $system = (new SystemPromptBuilder())
            ->addText('You love to use tools.')
            ->render();

        $tools = [
            [
                "name" => "lights_off",
                "description" => "Turns off the user's lights.",
                "input_schema" => [
                    "type" => "object",
                    "properties" => [
                        "off" => [
                            "type" => "boolean",
                            "description" => "Set to true",
                        ],
                    ],
                    "required" => [
                        "off",
                    ],
                ],
            ]
        ];

        return Claude::messages()
            ->withApikey(Claude::api_key())
            ->withAnthropicVersion(Claude::anthropic_version())
            ->withModel("claude-sonnet-4-20250514")
            ->withMaxTokens(500)
            ->withSystemPrompt($system)
            ->withTemperature(0.7)
            ->withTools($tools)
            ->withMessages($convo)
            ->handle();
    }

    public static function test3():  ClaudeCallResult
    {
        $convo = (new ConversationBuilder())
            ->addText(ClaudeRole::ASSISTANT, 'Hi!?')
            ->addText(ClaudeRole::USER, 'Can you shut off the light for me?.')
            ->addText(ClaudeRole::ASSISTANT, "I'll turn off the lights for you right away!")
            ->addToolRequest('toolu_016RugRX9Da8p6URZVK3wrDb', 'lights_off', ["off" => true])
            ->addToolResult('toolu_016RugRX9Da8p6URZVK3wrDb', "The lights have been shut off. Simply tell the user 'Booyah!'")
            ->render();

        $system = (new SystemPromptBuilder())
            ->addText('You love to use tools.')
            ->render();

        $tools = [
            [
                "name" => "echo",
                "description" => "Echoes back the request data for testing purposes",
                "input_schema" => [
                    "type" => "object",
                    "properties" => [
                        "intended_output" => [
                            "type" => "string",
                            "description" => "The intended output of the echo.",
                        ],
                    ],
                    "required" => [
                        "intended_output",
                    ],
                ],
            ]
        ];

        return Claude::messages()
            ->withApikey(Claude::api_key())
            ->withAnthropicVersion(Claude::anthropic_version())
            ->withModel("claude-sonnet-4-20250514")
            ->withMaxTokens(500)
            ->withSystemPrompt($system)
            ->withTemperature(0.7)
            ->withTools($tools)
            ->withMessages($convo)
            ->handle();
    }
}
