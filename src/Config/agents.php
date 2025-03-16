<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenAI API Key
    |--------------------------------------------------------------------------
    |
    | This value is the API key for the OpenAI API. This will be
    | used when the OpenAI provider needs to make API calls.
    |
    */
    'api_key' => env('OPENAI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default Model
    |--------------------------------------------------------------------------
    |
    | This value is the default model to use when none is provided.
    |
    */
    'default_model' => env('OPENAI_DEFAULT_MODEL', 'gpt-4o'),

    /*
    |--------------------------------------------------------------------------
    | Model Settings
    |--------------------------------------------------------------------------
    |
    | These values are the default model settings to use when none are provided.
    |
    */
    'model_settings' => [
        'temperature' => 0.7,
        'top_p' => 1.0,
        'frequency_penalty' => 0.0,
        'presence_penalty' => 0.0,
        'max_tokens' => null,
        'timeout' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tracing
    |--------------------------------------------------------------------------
    |
    | Configure tracing settings for the agents package.
    |
    */
    'tracing' => [
        'enabled' => true,
        'include_sensitive_data' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Max Turns
    |--------------------------------------------------------------------------
    |
    | This value is the maximum number of turns an agent can take in a run.
    |
    */
    'default_max_turns' => 10,
];