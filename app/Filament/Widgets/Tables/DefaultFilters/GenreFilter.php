<?php

namespace App\Filament\Widgets\Tables\DefaultFilters;

use App\Models\Genre;
use Filament\Forms\Set;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;

class GenreFilter
{
    public static function get(): SelectFilter
    {
        return SelectFilter::make('genres')
            ->multiple()
            ->label('Toon enkel')
            ->options(Genre::query()
                ->where('name', '!=', '\N')
                ->where('name', '!=', 'undefined')
                ->where('name', '!=', 'Adult')
                ->pluck('name', 'id')
            )
            ->native(false)
            ->searchable();
    }
}
