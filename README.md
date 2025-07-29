# LLMSpeak Anthropic Claude

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net/releases/)
[![Laravel](https://img.shields.io/badge/Laravel-10.x%7C11.x%7C12.x-red.svg)](https://laravel.com)
[![Total Downloads](https://img.shields.io/packagist/dt/llm-speak/anthropic-claude.svg?style=flat-square)](https://packagist.org/packages/llm-speak/anthropic-claude)

**LLMSpeak Anthropic Claude** is a Laravel package that provides a fluent, Laravel-native interface for integrating with Anthropic's Claude AI models. Built as part of the LLMSpeak ecosystem, it offers seamless integration with Laravel applications through automatic service discovery and expressive request builders.

> **Note:** This package is part of the larger [LLMSpeak ecosystem](https://github.com/projectsaturnstudios/llm-speak). For universal provider switching and standardized interfaces, check out the [LLMSpeak Core](https://github.com/projectsaturnstudios/llm-speak-core) package.

## Table of Contents
- [Features](#features)
- [Get Started](#get-started)
- [Usage](#usage)
  - [Basic Request](#basic-request)
  - [Fluent Request Building](#fluent-request-building)
  - [System Instructions](#system-instructions)
  - [Tool Calling](#tool-calling)
  - [Streaming Responses](#streaming-responses)
  - [Advanced Configuration](#advanced-configuration)
  - [Response Handling](#response-handling)
  - [Testing](#testing)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Security](#security)
- [Credits](#credits)
- [License](#license)

## Features

- **🚀 Laravel Native**: Full Laravel integration with automatic service discovery
- **🔧 Fluent Interface**: Expressive request builders with method chaining
- **📊 Laravel Data**: Powered by Spatie Laravel Data for robust data validation
- **🛠️ Tool Support**: Complete function calling capabilities
- **💨 Streaming**: Support for real-time streaming responses
- **🎯 Type Safety**: Full PHP 8.2+ type declarations and IDE support
- **🔐 Secure**: Built-in API key management and request validation

## Get Started

> **Requires [PHP 8.2+](https://php.net/releases/) and Laravel 10.x/11.x/12.x**

Install the package via [Composer](https://getcomposer.org/):

```bash
composer require llm-speak/anthropic-claude
```

The package will automatically register itself via Laravel's package discovery.

### Environment Configuration

Add your Anthropic API key to your `.env` file:

```env
ANTHROPIC_API_KEY=your_api_key_here
```

> **Note:** The package currently uses Anthropic API version `2023-06-01` (hardcoded).

## Usage

### Basic Request

The simplest way to send a message to Claude:

```php
use LLMSpeak\Anthropic\ClaudeMessageRequest;

$request = new ClaudeMessageRequest(
    model: 'claude-3-5-sonnet-20241022',
    messages: [
        ['role' => 'user', 'content' => 'Hello, Claude!']
    ]
);

$response = $request->post();

echo $response->getTextContent(); // "Hello! How can I assist you today?"
```

### Fluent Request Building

Build complex requests using the fluent interface:

```php
use LLMSpeak\Anthropic\ClaudeMessageRequest;

$request = new ClaudeMessageRequest(
    model: 'claude-3-5-sonnet-20241022',
    messages: [
        ['role' => 'user', 'content' => 'Explain quantum computing']
    ]
)
->setMaxTokens(1000)
->setTemperature(0.7)
->setSystem('You are a helpful physics professor.')
->setTopK(50)
->setTopP(0.9);

$response = $request->post();

// Access response properties
echo $response->id;              // msg_01ABC123...
echo $response->model;           // claude-3-5-sonnet-20241022
echo $response->stop_reason;     // end_turn
echo $response->getTotalTokens(); // 850
```

### Batch Configuration

Set multiple parameters at once:

```php
$request = new ClaudeMessageRequest(
    model: 'claude-3-5-sonnet-20241022',
    messages: $messages
)->setMultiple([
    'max_tokens' => 1500,
    'temperature' => 0.8,
    'top_k' => 40,
    'stop_sequences' => ['Human:', 'Assistant:']
]);
```

### System Instructions

Claude supports rich system instructions for context and behavior:

```php
$systemPrompt = "You are Claude, an AI assistant created by Anthropic. " .
                "You are helpful, harmless, and honest. " .
                "Provide detailed explanations with examples.";

$request = new ClaudeMessageRequest(
    model: 'claude-3-5-sonnet-20241022',
    messages: [
        ['role' => 'user', 'content' => 'Explain machine learning']
    ]
)->setSystem($systemPrompt);

$response = $request->post();
```

### Tool Calling

Enable Claude to use external functions and tools:

```php
$tools = [
    [
        'name' => 'get_weather',
        'description' => 'Get current weather for a location',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'location' => [
                    'type' => 'string',
                    'description' => 'City and state/country'
                ],
                'unit' => [
                    'type' => 'string',
                    'enum' => ['celsius', 'fahrenheit'],
                    'description' => 'Temperature unit'
                ]
            ],
            'required' => ['location']
        ]
    ]
];

$request = new ClaudeMessageRequest(
    model: 'claude-3-5-sonnet-20241022',
    messages: [
        ['role' => 'user', 'content' => 'What\'s the weather in San Francisco?']
    ]
)
->setTools($tools)
->setToolChoice('auto');

$response = $request->post();

// Check if tools were used
if ($response->usedTools()) {
    $toolCalls = $response->getToolCalls();
    foreach ($toolCalls as $toolCall) {
        echo "Tool: {$toolCall['name']}\n";
        echo "Input: " . json_encode($toolCall['input']) . "\n";
    }
}
```

### Streaming Responses

Enable real-time streaming for long responses:

```php
$request = new ClaudeMessageRequest(
    model: 'claude-3-5-sonnet-20241022',
    messages: [
        ['role' => 'user', 'content' => 'Write a long story about space exploration']
    ]
)
->setStream(true)
->setMaxTokens(2000);

$response = $request->post();

// Stream will be handled by the MessagesEndpoint
// Check response for streaming data format
```

### Advanced Configuration

Configure advanced parameters for fine-tuned control:

```php
$request = new ClaudeMessageRequest(
    model: 'claude-3-5-sonnet-20241022',
    messages: $conversationHistory
)
->setMaxTokens(4000)
->setTemperature(0.6)
->setTopK(60)
->setTopP(0.95)
->setStopSequences(['[END]', '###'])
->setMetadata([
    'user_id' => 'user_123',
    'session_id' => 'session_456'
])
->setServiceTier('standard_only');

$response = $request->post();
```

## Response Handling

Access rich response data:

```php
$response = $request->post();

// Basic response info
$messageId = $response->id;
$modelUsed = $response->model;
$completionReason = $response->stop_reason;

// Content access
$textContent = $response->getTextContent();
$allContent = $response->content; // Raw content array

// Token usage
$inputTokens = $response->getInputTokens();
$outputTokens = $response->getOutputTokens();
$totalTokens = $response->getTotalTokens();

// Completion status
$isComplete = $response->completedNaturally();
$hitTokenLimit = $response->reachedTokenLimit();

// Convert to array for storage/processing
$responseArray = $response->toArray();
```

## Testing

The package provides testing utilities for mocking Anthropic responses:

```php
use LLMSpeak\Anthropic\ClaudeMessageRequest;
use LLMSpeak\Anthropic\ClaudeMessageResponse;

// Create a mock response
$mockResponse = new ClaudeMessageResponse(
    id: 'msg_test_123',
    type: 'message',
    role: 'assistant',
    content: [
        ['type' => 'text', 'text' => 'Test response']
    ],
    model: 'claude-3-5-sonnet-20241022',
    stop_reason: 'end_turn',
    stop_sequence: null,
    usage: [
        'input_tokens' => 10,
        'output_tokens' => 15
    ]
);

// Test your application logic with the mock
$this->assertEquals('Test response', $mockResponse->getTextContent());
```

## Credits

- [Project Saturn Studios](https://github.com/projectsaturnstudios)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

---

**Part of the LLMSpeak Ecosystem** - Made with ADHD by [Project Saturn Studios](https://projectsaturnstudios.com)
