<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Provider
    |--------------------------------------------------------------------------
    |
    | Any text provider of the Laravel AI SDK that accepts images, e.g. "openai",
    | "anthropic" or "gemini". Its API key is set in config/ai.php (OPENAI_API_KEY,
    | ANTHROPIC_API_KEY, GEMINI_API_KEY). Empty uses the default of config/ai.php.
    | Empty model uses the provider's cheapest model.
    |
    */
    'provider' => env('AI_ALT_PROVIDER'),
    'model' => env('AI_ALT_MODEL'),

    /*
    |--------------------------------------------------------------------------
    | Alt Text Generation Settings
    |--------------------------------------------------------------------------
    */
    'language' => env('AI_ALT_LANGUAGE', 'English'),
];
