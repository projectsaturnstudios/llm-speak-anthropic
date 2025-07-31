<?php

namespace LLMSpeak\Anthropic\Providers;

use Illuminate\Support\ServiceProvider;

class AnthropicLLMSpeakServiceProvider extends ServiceProvider
{
    protected array $config = [
        'llms.chat-providers.drivers.anthropic' => __DIR__ .'/../../config/anthropic.php',
    ];

    public function register(): void
    {
        $this->registerConfigs();
    }

    public function boot(): void
    {
        $this->publishConfigs();
        //Anthropic::boot();
    }

    protected function publishConfigs() : void
    {
        $this->publishes([
            $this->config['llms.chat-providers.drivers.anthropic'] => config_path('llms/anthropic.php'),
        ], ['llms', 'llms.anthropic']);
    }

    protected function registerConfigs() : void
    {
        foreach ($this->config as $key => $path) {
            $this->mergeConfigFrom($path, $key);
        }
    }

}
