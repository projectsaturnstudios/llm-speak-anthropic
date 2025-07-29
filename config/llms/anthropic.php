<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Anthropic API Key
    |--------------------------------------------------------------------------
    |
    | Here you may specify your Anthropic API Key. This will be used to authenticate
    | with the Anthropic API - you can find your API key on your Anthropic
    | dashboard, at https://console.anthropic.com/settings/keys.
    */

    'api_key' => env('ANTHROPIC_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Anthropic API URL
    |--------------------------------------------------------------------------
    |
    | Here you may specify the base URL for the Anthropic API. The default
    | is the official Anthropic API endpoint, but you can change it if needed.
    |
    |
    */

    'api_url' => env('ANTHROPIC_URL', 'https://api.anthropic.com/v1/'),

    /*
     |--------------------------------------------------------------------------
     | Anthropic Extra Headers
     |--------------------------------------------------------------------------
     |
     | Here you may specify any extra headers that should be sent with each request
     | to the Anthropic API. This is useful for setting specific API versions or
     | enabling beta features. The default headers include the version and beta
     | headers that Anthropic recommends.
     |
     */
    'extra_headers' => [
        'anthropic-version' => '2023-06-01',
        'anthropic-beta' => null,
    ],
];
