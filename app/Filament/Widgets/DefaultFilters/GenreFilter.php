<?php

namespace App\Filament\Widgets\DefaultFilters;

use App\Models\Genre;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Set;

class GenreFilter
{
    public static function get(): Select
    {
        return Select::make('genres')
            ->multiple()
            ->label('Toon enkel')
            ->options(Genre::query()
                ->where('name', '!=', '\N')
                ->where('name', '!=', 'Adult')
                ->pluck('name', 'id'))
            ->maxItems(10)
            ->hintAction(
                Action::make('clearField')
                    ->label('Reset')
                    ->icon('heroicon-m-trash')
                    ->action(function (Set $set) {
                        $set('genres', []);
                    })
            )
            ->native(false)
            ->searchable();
    }
}
