<?php

namespace Croox\StatamicAiAlt\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Statamic\Facades\Asset;
use OpenAI\Laravel\Facades\OpenAI;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;
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

        // Create an image instance and resize it
        $image = Image::make($asset->contents());

        // Resize to max 1200px on longest side
        $width = $image->width();
        $height = $image->height();

        if ($width > $height) {
            $image->resize(1200, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        } else {
            $image->resize(null, 1200, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        // Convert to WebP and get contents
        $processedImage = $image->encode('webp', 80);
        $base64Image = base64_encode($processedImage);

        $response = OpenAI::chat()->create([
            'model' => config('statamic-ai-alt.openai.model'),
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "Provide alt text in " . config('statamic-ai-alt.language', 'English') . "Do not say 'Image of' or 'Photo of' or anything like that but just describe the image. Be concise and stay under 500 characters in 1-2 sentences. Finish with a period."
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:image/webp;base64,{$base64Image}",
                            ]
                        ]
                    ]
                ]
            ],
            'max_tokens' => 100
        ]);

        $altText = $response->choices[0]->message->content;
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
