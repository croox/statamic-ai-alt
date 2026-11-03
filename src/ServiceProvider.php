<?php

namespace Croox\StatamicAiAlt;

use Statamic\Providers\AddonServiceProvider;
use Croox\StatamicAiAlt\Actions\GenerateAltText;

/**
 * @method void publishes(array $paths, string $group = null)
 * @property \Illuminate\Foundation\Application $app
 */
class ServiceProvider extends AddonServiceProvider
{
    protected $actions = [
        GenerateAltText::class
    ];

    public function bootAddon()
    {
        $this->publishes([
            __DIR__.'/../config/statamic-ai-alt.php' => config_path('statamic-ai-alt.php'),
        ], 'statamic-ai-alt-config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/statamic-ai-alt.php', 'statamic-ai-alt');

        config([
            'openai.api_key' => config('statamic-ai-alt.openai.api_key'),
            'openai.model' => config('statamic-ai-alt.openai.model')
        ]);

        $this->app->register(\OpenAI\Laravel\ServiceProvider::class);
    }
}
