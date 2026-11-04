<?php

namespace Croox\StatamicAiAlt\Jobs;

use Croox\StatamicAiAlt\AltTextGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Statamic\Facades\Asset;
use Throwable;

class GenerateAltTextJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $assetId;

    public function __construct($assetId)
    {
        $this->assetId = $assetId;
    }

    public function handle(AltTextGenerator $generator)
    {
        if (!$asset = Asset::find($this->assetId)) {
            return;
        }

        $altText = $generator->generate($asset);

        if ($altText === '') {
            Log::warning('AI Alt Text Generation returned no text', ['asset_id' => $this->assetId]);

            return;
        }

        $asset->set('alt', $altText);
        $asset->save();
    }

    public function failed(Throwable $exception)
    {
        Log::error('AI Alt Text Generation failed', [
            'asset_id' => $this->assetId,
            'exception' => $exception->getMessage()
        ]);
    }
}
