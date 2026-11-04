<?php

namespace Croox\StatamicAiAlt\Actions;

use Statamic\Actions\Action;
use Croox\StatamicAiAlt\Jobs\GenerateAltTextJob;
use Illuminate\Support\Str;
use Statamic\Assets\Asset;

class GenerateAltText extends Action
{
    public static function title()
    {
        return __('Generate Alt Text', [], static::language());
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

        return ['message' => trans_choice(
            'Alt text generation queued for :count image|Alt text generation queued for :count images',
            $count,
            ['count' => $count],
            static::language()
        )];
    }

    /**
     * Laravel looks a locale up as it is, so de_DE would miss de.json. The English
     * text is the key, so a language without a translation still reads properly.
     */
    protected static function language()
    {
        return Str::before(app()->getLocale(), '_');
    }
}
