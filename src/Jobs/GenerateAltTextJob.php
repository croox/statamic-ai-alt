<?php

namespace Croox\StatamicAiAlt\Jobs;

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
use OpenAI\Laravel\Facades\OpenAI;
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
        $dataUri = ImageManager::usingDriver($driver)
            ->decodeBinary($asset->contents())
            ->scaleDown(1200, 1200)
            ->encodeUsingFormat(Format::WEBP, quality: 80)
            ->toDataUri();

        $response = OpenAI::chat()->create([
            'model' => config('statamic-ai-alt.openai.model'),
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "Provide alt text in " . config('statamic-ai-alt.language', 'English') . ". Do not say 'Image of' or 'Photo of' or anything like that but just describe the image. Be concise and stay under 500 characters in 1-2 sentences. Finish with a period."
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => (string) $dataUri,
                            ]
                        ]
                    ]
                ]
            ],
            'max_completion_tokens' => 200
        ]);

        $altText = trim($response->choices[0]->message->content ?? '');

        if ($altText === '') {
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
