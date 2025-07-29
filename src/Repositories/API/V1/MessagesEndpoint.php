<?php

namespace LLMSpeak\Anthropic\Repositories\API\V1;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class MessagesEndpoint
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
      * Handle Anthropic Messages API request
      *
      * @param string $url - Full API URL (e.g., "https://api.anthropic.com/v1/messages")
      * @param string $apiKey - Anthropic API key
      * @param string $model - Model identifier (e.g., "claude-3-5-sonnet-20241022")
      * @param array $messages - Conversation messages array
      * @param int $maxTokens - Maximum tokens to generate
      * @param string|null $system - System prompt (Anthropic-specific)
      * @param array|null $tools - Function definitions array
      * @param string|null $toolChoice - Tool choice strategy ("auto", "any", "tool", or object)
      * @param float|null $temperature - Randomness (0.0 to 1.0)
      * @param bool|null $stream - Enable streaming response
      * @param array|null $responseFormat - Response format configuration
      * @param bool|null $parallelFunctionCalling - Enable parallel function calls
      * @param array|null $thinking - Configuration for enabling Claude's extended thinking
      * @param int|null $topK - Only sample from top K options for each token (advanced use)
      * @param float|null $topP - Nucleus sampling probability threshold (0.0 to 1.0, advanced use)
      * @param array|null $stopSequences - Custom text sequences that cause model to stop
      * @param string|null $serviceTier - Service tier ("auto" or "standard_only")
      * @param array|null $metadata - Request metadata object
      * @param string|null $container - Container identifier for reuse across requests
      * @param array|null $mcpServers - MCP servers to utilize in this request
      * @return array - Contains 'headers', 'status_code', 'body' keys
      * @throws GuzzleException
      */
    public function handle(
        string $url,
        string $apiKey,
        string $model,
        array $messages,
        int $maxTokens,

        ?string $system = null,
        ?array $tools = null,
        ?string $toolChoice = null,
        ?float $temperature = 1.0,
        ?bool $stream = false,
        ?array $responseFormat = null,
        ?bool $parallelFunctionCalling = null,
        ?array $thinking = null,
        ?int $topK = null,
        ?float $topP = null,
        ?array $stopSequences = null,
        ?string $serviceTier = null,
        ?array $metadata = null,
        ?string $container = null,
        ?array $mcpServers = null
    ): array {
        // Build request payload
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => $maxTokens,
        ];

        // Add optional parameters only if provided
        if ($system !== null) {
            $payload['system'] = $system;
        }

        if ($tools !== null) {
            $payload['tools'] = $tools;
            
            // Only include tool_choice when tools are provided
            if ($toolChoice !== null) {
                $payload['tool_choice'] = $toolChoice;
            }
        }

        if ($temperature !== null) {
            $payload['temperature'] = $temperature;
        }

        if ($stream !== null) {
            $payload['stream'] = $stream;
        }

        if ($responseFormat !== null) {
            $payload['response_format'] = $responseFormat;
        }

        // Only include parallel_function_calling when tools are provided
        if ($tools !== null && $parallelFunctionCalling !== null) {
            $payload['parallel_function_calling'] = $parallelFunctionCalling;
        }

        // Extended thinking configuration
        if ($thinking !== null) {
            $payload['thinking'] = $thinking;
        }

        // Advanced sampling parameters
        if ($topK !== null) {
            $payload['top_k'] = $topK;
        }

        if ($topP !== null) {
            $payload['top_p'] = $topP;
        }

        // Stop sequences
        if ($stopSequences !== null) {
            $payload['stop_sequences'] = $stopSequences;
        }

        // Service tier configuration
        if ($serviceTier !== null) {
            $payload['service_tier'] = $serviceTier;
        }

        // Request metadata
        if ($metadata !== null) {
            $payload['metadata'] = $metadata;
        }

        // Container identifier
        if ($container !== null) {
            $payload['container'] = $container;
        }

        // MCP servers
        if ($mcpServers !== null) {
            $payload['mcp_servers'] = $mcpServers;
        }

        // Make the request
        $response = $this->client->post($url, [
            'headers' => [
                'x-api-key' => $apiKey,
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01',
            ],
            'json' => $payload,
        ]);

        // Get response body
        $body = $response->getBody()->getContents();

        // Return complete response data
        return [
            'status_code' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => json_decode($body, true),
            'raw_body' => $body,
        ];
    }
}
