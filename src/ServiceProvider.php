<?php

namespace Croox\StatamicAiAlt;

use Statamic\Providers\AddonServiceProvider;
use Croox\StatamicAiAlt\Actions\GenerateAltText;

class ServiceProvider extends AddonServiceProvider
{
    protected $actions = [
        GenerateAltText::class
    ];

    public function register()
    {
        // Statamic merges and publishes config/statamic-ai-alt.php itself, but only
        // on boot. The key has to be handed to openai-php before that.
        $this->mergeConfigFrom(__DIR__.'/../config/statamic-ai-alt.php', 'statamic-ai-alt');

        config(['openai.api_key' => config('statamic-ai-alt.openai.api_key')]);
    }
}
