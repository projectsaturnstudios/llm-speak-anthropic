```php
use LLMSpeak\Anthropic\Support\Facades\Claude;

Claude::messages() <--- ClaudeMessagesAPIRepository Instance
    ->withApiKey($config['api_key']) <--- ClaudeMessagesAPIRepository Instance
    ->withAnthropicVersion($config['extra_headers']['anthropic-version']) <--- ClaudeMessagesAPIRepository Instance
    ->withModel($model) <--- ClaudeMessagesAPIRepository Instance
    ->withMaxTokens($max_tokens) <--- ClaudeMessagesAPIRepository Instance
    ->withSystemPrompt($prompt) <--- ClaudeMessagesAPIRepository Instance
    ->withTools($temperature) <--- ClaudeMessagesAPIRepository Instance
    ->withTemperature($temperature) <--- ClaudeMessagesAPIRepository Instance
    ->withMessages($messages) <--- MessagesEndpoint Instance
    ->handle();
```
