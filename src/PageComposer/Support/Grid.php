<?php

namespace Flobbos\PageComposer\Support;

use Illuminate\Support\Arr;

class Grid
{
    /**
     * Used when a published config predates the column_widths key.
     */
    private const DEFAULT_WIDTHS = [
        12 => 'w-full',
        11 => 'w-11/12',
        10 => 'w-5/6',
        9 => 'w-3/4',
        8 => 'w-2/3',
        7 => 'w-7/12',
        6 => 'w-1/2',
        5 => 'w-5/12',
        4 => 'w-1/3',
        3 => 'w-1/4',
        2 => 'w-1/6',
        1 => 'w-1/12',
    ];

    /**
     * Tailwind width class for a column spanning $size of 12.
     */
    public static function columnWidth(int $size): string
    {
        return Arr::get(config('pagecomposer.column_widths', self::DEFAULT_WIDTHS), $size, 'w-full');
    }
}
