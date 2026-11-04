<?php

namespace Croox\StatamicAiAlt\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

/**
 * Describes one image. Without a model in the config it takes the provider's
 * cheapest one, which is plenty for a sentence or two. The token limit leaves
 * room for models that reason before they answer.
 */
#[UseCheapestModel]
#[MaxTokens(1000)]
class AltTextAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return "Provide alt text in " . config('statamic-ai-alt.language', 'English') . ". Do not say 'Image of' or 'Photo of' or anything like that but just describe the image. Be concise and stay under 500 characters in 1-2 sentences. Finish with a period.";
    }
}
