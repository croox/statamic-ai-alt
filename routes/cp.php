<?php

use Croox\StatamicAiAlt\Http\Controllers\GenerateAltTextController;
use Illuminate\Support\Facades\Route;

Route::post('statamic-ai-alt/generate', GenerateAltTextController::class)->name('statamic-ai-alt.generate');
