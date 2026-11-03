<?php

namespace Croox\StatamicAiAlt\Actions;

use Statamic\Actions\Action;
use Croox\StatamicAiAlt\Jobs\GenerateAltTextJob;
use Statamic\Assets\Asset;

class GenerateAltText extends Action
{
    public static function title()
    {
        return __('statamic-ai-alt::messages.title');
    }

    public function visibleTo($item)
    {
        return $item instanceof Asset && $item->isImage();
    }

    public function authorize($user, $item)
    {
        return $user->can('edit', $item);
    }

    public function run($items, $values)
    {
        $count = 0;

        foreach ($items as $item) {
            if ($item instanceof Asset) {
                GenerateAltTextJob::dispatch($item->id());
                $count++;
            }
        }

        return ['message' => trans_choice('statamic-ai-alt::messages.success', $count, ['count' => $count])];
    }
}
