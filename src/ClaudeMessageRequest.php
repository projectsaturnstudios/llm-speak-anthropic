<?php

namespace LLMSpeak\Anthropic;

use Spatie\LaravelData\Data;
use LLMSpeak\Anthropic\Repositories\API\V1\MessagesEndpoint;
use GuzzleHttp\Exception\GuzzleException;

/**
 * ClaudeMessageRequest - Anthropic API Request Builder
 *
 * Usage Examples:
 *
 * // Traditional setters
 * $request = new ClaudeMessageRequest('claude-3-5-sonnet-20241022', $messages)
 *     ->setTemperature(0.8)
 *     ->setMaxTokens(1000)
 *     ->setStream(true);
 *
 * // Generic set() method
 * $request = $request->set('temperature', 0.8)->set('max_tokens', 1000);
 *
 * // Batch setting
 * $request = $request->setMultiple([
 *     'temperature' => 0.8,
 *     'max_tokens' => 1000,
 *     'stream' => true,
 *     'top_k' => 5
 * ]);
 *
 * // Magic methods (camelCase gets converted to snake_case)
 * $request = $request->setTemperature(0.8)->setMaxTokens(1000)->setTopK(5);
 *
 * // Convert to MessagesEndpoint parameters
 * $params = $request->toArray();
 * $response = (new MessagesEndpoint())->handle(...$params);
 *
 * // Or make direct API call
 * $response = $request->post(); // Returns ClaudeMessageResponse
 */
class ClaudeMessageRequest extends Data
{
    protected string $url;
    protected string $api_key;

    public function __construct(
        public readonly string $model,
        public readonly array $messages,
        public readonly ?string $max_tokens = null,

        public readonly ?string $system = null,
        public readonly ?array $tools = null,
        public readonly ?string $tool_choice = null,
        public readonly ?float $temperature = 1.0,
        public readonly ?bool $stream = false,
        public readonly ?array $response_format = null,
        public readonly ?bool $parallel_function_calling = null,
        public readonly ?array $thinking = null,
        public readonly ?int $top_k = null,
        public readonly ?float $top_p = null,
        public readonly ?array $stop_sequences = null,
        public readonly ?string $service_tier = null,
        public readonly ?array $metadata = null,
        public readonly ?string $container = null,
        public readonly ?array $mcp_servers = null
    )
    {
        $this->api_key = env('ANTHROPIC_API_KEY');
        $this->url = config('llms.providers.drivers.anthropic.base_url')."/messages";
    }

    /**
     * Generic method to set any property and return a new instance
     */
    public function set(string $property, mixed $value): self
    {
        $currentData = [
            'model' => $this->model,
            'messages' => $this->messages,
            'max_tokens' => $this->max_tokens,
            'system' => $this->system,
            'tools' => $this->tools,
            'tool_choice' => $this->tool_choice,
            'temperature' => $this->temperature,
            'stream' => $this->stream,
            'response_format' => $this->response_format,
            'parallel_function_calling' => $this->parallel_function_calling,
            'thinking' => $this->thinking,
            'top_k' => $this->top_k,
            'top_p' => $this->top_p,
            'stop_sequences' => $this->stop_sequences,
            'service_tier' => $this->service_tier,
            'metadata' => $this->metadata,
            'container' => $this->container,
            'mcp_servers' => $this->mcp_servers,
        ];

        $currentData[$property] = $value;

        return new self(...$currentData);
    }

    /**
     * Batch setter - set multiple properties at once
     */
    public function setMultiple(array $properties): self
    {
        $instance = $this;
        foreach ($properties as $property => $value) {
            $instance = $instance->set($property, $value);
        }
        return $instance;
    }

    /**
     * Magic method approach - could replace all setter methods
     * Usage: $request->setTemperature(0.8) or $request->setTopK(5)
     */
    public function __call(string $method, array $arguments): self
    {
        if (str_starts_with($method, 'set')) {
            $property = strtolower(preg_replace('/([A-Z])/', '_$1', substr($method, 4)));
            return $this->set($property, $arguments[0] ?? null);
        }

        throw new \BadMethodCallException("Method {$method} does not exist");
    }

    /**
     * Convenience methods using the generic set() method
     */
    public function setMaxTokens(int $max_tokens): self
    {
        return $this->set('max_tokens', (string)$max_tokens);
    }

    public function setSystemPrompt(string $system): self
    {
        return $this->set('system', $system);
    }

    public function setTools(array $tools): self
    {
        return $this->set('tools', $tools);
    }

    public function setToolChoice(string $tool_choice): self
    {
        return $this->set('tool_choice', $tool_choice);
    }

    public function setTemperature(float $temperature): self
    {
        return $this->set('temperature', $temperature);
    }

    public function setStream(bool $stream): self
    {
        return $this->set('stream', $stream);
    }

    public function setResponseFormat(array $response_format): self
    {
        return $this->set('response_format', $response_format);
    }

    public function setParallelFunctionCalling(bool $parallel_function_calling): self
    {
        return $this->set('parallel_function_calling', $parallel_function_calling);
    }

    public function setThinking(array $thinking): self
    {
        return $this->set('thinking', $thinking);
    }

    public function setTopK(int $top_k): self
    {
        return $this->set('top_k', $top_k);
    }

    public function setTopP(float $top_p): self
    {
        return $this->set('top_p', $top_p);
    }

    public function setStopSequences(array $stop_sequences): self
    {
        return $this->set('stop_sequences', $stop_sequences);
    }

    public function setServiceTier(string $service_tier): self
    {
        return $this->set('service_tier', $service_tier);
    }

    public function setMetadata(array $metadata): self
    {
        return $this->set('metadata', $metadata);
    }

    public function setContainer(string $container): self
    {
        return $this->set('container', $container);
    }

    public function setMcpServers(array $mcp_servers): self
    {
        return $this->set('mcp_servers', $mcp_servers);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'apiKey' => $this->api_key,
            'model' => $this->model,
            'messages' => $this->messages,
            'maxTokens' => (int) $this->max_tokens,
            'system' => $this->system,
            'tools' => $this->tools,
            'toolChoice' => $this->tool_choice,
            'temperature' => $this->temperature,
            'stream' => $this->stream,
            'responseFormat' => $this->response_format,
            'parallelFunctionCalling' => $this->parallel_function_calling,
            'thinking' => $this->thinking,
            'topK' => $this->top_k,
            'topP' => $this->top_p,
            'stopSequences' => $this->stop_sequences,
            'serviceTier' => $this->service_tier,
            'metadata' => $this->metadata,
            'container' => $this->container,
            'mcpServers' => $this->mcp_servers,
        ];
    }

    public function post(): ClaudeMessageResponse
    {
        try {
            // Get the parameters for the MessagesEndpoint
            $params = $this->toArray();

            // Make the API call using MessagesEndpoint
            $endpoint = new MessagesEndpoint();
            $rawResponse = $endpoint->handle(...$params);

            // Validate the response structure
            if (!isset($rawResponse['status_code']) || $rawResponse['status_code'] !== 200) {
                throw new \Exception(
                    'API call failed with status: ' . ($rawResponse['status_code'] ?? 'unknown') .
                    '. Error: ' . ($rawResponse['error'] ?? 'No error details provided')
                );
            }

            // Handle streaming vs non-streaming responses
            if ($this->stream === true) {
                // For streaming, parse the SSE events and reconstruct final response
                if (!isset($rawResponse['raw_body'])) {
                    throw new \Exception('Invalid streaming response: missing raw_body');
                }

                return $this->parseStreamingResponse($rawResponse['raw_body']);
            } else {
                // For non-streaming, use the body directly
                if (!isset($rawResponse['body']) || !is_array($rawResponse['body'])) {
                    throw new \Exception('Invalid API response: missing or invalid body');
                }

                return ClaudeMessageResponse::fromApiResponse($rawResponse['body']);
            }

        } catch (GuzzleException $e) {
            throw new \Exception('HTTP request failed: ' . $e->getMessage(), 0, $e);
        } catch (\Exception $e) {
            // Re-throw our own exceptions, wrap any others
            if (str_starts_with($e->getMessage(), 'API call failed') ||
                str_starts_with($e->getMessage(), 'Invalid API response') ||
                str_starts_with($e->getMessage(), 'Invalid streaming response')) {
                throw $e;
            }
            throw new \Exception('Unexpected error during API call: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Parse Server-Sent Events streaming response into final ClaudeMessageResponse
     */
    private function parseStreamingResponse(string $rawBody): ClaudeMessageResponse
    {
        $lines = explode("\n", $rawBody);
        $finalMessage = null;
        $contentBlocks = [];
        $currentBlockIndex = -1;

        foreach ($lines as $line) {
            $line = trim($line);

            if (str_starts_with($line, 'data: ')) {
                $jsonData = substr($line, 6);

                if ($jsonData === '[DONE]') {
                    break;
                }

                $data = json_decode($jsonData, true);
                if (!$data) continue;

                // Handle different event types
                switch ($data['type'] ?? '') {
                    case 'message_start':
                        $finalMessage = $data['message'];
                        break;

                    case 'content_block_start':
                        $currentBlockIndex++;
                        $contentBlocks[$currentBlockIndex] = $data['content_block'];
                        break;

                    case 'content_block_delta':
                        if ($currentBlockIndex >= 0 && isset($data['delta'])) {
                            $delta = $data['delta'];
                            if (isset($delta['text'])) {
                                // Append text delta
                                if (!isset($contentBlocks[$currentBlockIndex]['text'])) {
                                    $contentBlocks[$currentBlockIndex]['text'] = '';
                                }
                                $contentBlocks[$currentBlockIndex]['text'] .= $delta['text'];
                            }
                        }
                        break;

                    case 'message_delta':
                        if (isset($data['delta']) && $finalMessage) {
                            // Update final message with delta information
                            $finalMessage = array_merge($finalMessage, $data['delta']);
                        }
                        break;

                    case 'error':
                        $error = $data['error'] ?? $data;
                        $errorType = $error['type'] ?? 'unknown';
                        $errorMessage = $error['message'] ?? 'Unknown streaming error';
                        throw new \Exception("Streaming API error ({$errorType}): {$errorMessage}");
                }
            }
        }

        if (!$finalMessage) {
            throw new \Exception('Invalid streaming response: no final message found');
        }

        // Reconstruct the final response with assembled content blocks
        $finalMessage['content'] = array_values($contentBlocks);

        return ClaudeMessageResponse::fromApiResponse($finalMessage);
    }
}
