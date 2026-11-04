<?php

namespace Croox\StatamicAiAlt;

use Croox\StatamicAiAlt\Agents\AltTextAgent;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use Laravel\Ai\Files\Image;
use Statamic\Contracts\Assets\Asset;

class AltTextGenerator
{
    public function generate(Asset $asset): string
    {
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

        return trim($response->text);
    }
}
