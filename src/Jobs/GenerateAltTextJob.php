<?php

namespace Croox\StatamicAiAlt\Jobs;

use Croox\StatamicAiAlt\Agents\AltTextAgent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use Laravel\Ai\Files\Image;
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

    public function handle()
    {
        if (!$asset = Asset::find($this->assetId)) {
            return;
        }

        // Same driver Statamic uses for its own image manipulation. The
        // contents come from the asset's disk, so remote disks work as well.
        $driver = config('statamic.assets.image_manipulation.driver', 'gd') === 'imagick'
            ? ImagickDriver::class
            : GdDriver::class;

        // Shrink to max 1200px on the longest side (never upscale) and send as WebP
        $encoded = ImageManager::usingDriver($driver)
            ->decodeBinary($asset->contents())
            ->scaleDown(1200, 1200)
            ->encodeUsingFormat(Format::WEBP, quality: 80);

        $response = (new AltTextAgent)->prompt(
            'Describe this image.',
            attachments: [Image::fromBase64(base64_encode((string) $encoded), 'image/webp')],
            provider: config('statamic-ai-alt.provider') ?: null,
            model: config('statamic-ai-alt.model') ?: null,
        );

        $altText = trim($response->text);

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
