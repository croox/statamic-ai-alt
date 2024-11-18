<?php

namespace Vendor\StatamicAiAlt;

use Statamic\Providers\AddonServiceProvider;
use OpenAI\Laravel\Facades\OpenAI;
use Vendor\StatamicAiAlt\Actions\GenerateAltText;

class ServiceProvider extends AddonServiceProvider
{
    protected $actions = [
        GenerateAltText::class
    ];

    protected $scripts = [
        __DIR__.'/../resources/js/cp.js'
    ];

    public function bootAddon()
    {
        parent::bootAddon();

        $this->publishes([
            __DIR__.'/../config/statamic-ai-alt.php' => config_path('statamic-ai-alt.php'),
            __DIR__.'/../config/openai.php' => config_path('openai.php'),
        ], 'config');
    }

    public function register()
    {
        parent::register();

        // Merge configs
        $this->mergeConfigFrom(__DIR__.'/../config/statamic-ai-alt.php', 'statamic-ai-alt');
        $this->mergeConfigFrom(__DIR__.'/../config/openai.php', 'openai');
    }
}