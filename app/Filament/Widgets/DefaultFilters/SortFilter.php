<?php

namespace App\Filament\Widgets\DefaultFilters;

use Filament\Forms\Components\Toggle;

class SortFilter
{
    public static function get(): Toggle
    {
        return Toggle::make('sort')
            ->label('Sorteer hoog -> laag')
            ->required()
            ->default(true);
    }
}
