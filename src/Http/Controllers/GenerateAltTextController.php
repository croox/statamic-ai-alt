<?php

namespace Croox\StatamicAiAlt\Http\Controllers;

use Croox\StatamicAiAlt\AltTextGenerator;
use Illuminate\Http\Request;
use Statamic\Facades\Asset;
use Statamic\Http\Controllers\CP\CpController;

/**
 * Behind the wand on the alt field: it answers with the text instead of saving
 * it, so the field fills up right away and the editor decides what to keep.
 */
class GenerateAltTextController extends CpController
{
    public function __invoke(Request $request, AltTextGenerator $generator)
    {
        $request->validate(['id' => 'required|string']);

        abort_unless($asset = Asset::find($request->id), 404);

        $this->authorize('edit', $asset);

        abort_unless($asset->isImage(), 422);

        $text = $generator->generate($asset);

        abort_if($text === '', 422);

        return ['text' => $text];
    }
}
