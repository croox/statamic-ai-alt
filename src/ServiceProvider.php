<?php

namespace Croox\StatamicAiAlt;

use Statamic\Providers\AddonServiceProvider;
use Croox\StatamicAiAlt\Actions\GenerateAltText;

class ServiceProvider extends AddonServiceProvider
{
    protected $actions = [
        GenerateAltText::class
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $scripts = [
        __DIR__.'/../resources/js/cp.js',
    ];

    public function bootAddon()
    {
        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang');
    }
}
