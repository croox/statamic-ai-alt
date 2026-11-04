<?php

namespace Croox\StatamicAiAlt;

use Statamic\Providers\AddonServiceProvider;
use Croox\StatamicAiAlt\Actions\GenerateAltText;

class ServiceProvider extends AddonServiceProvider
{
    protected $actions = [
        GenerateAltText::class
    ];

    public function bootAddon()
    {
        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang');
    }
}
