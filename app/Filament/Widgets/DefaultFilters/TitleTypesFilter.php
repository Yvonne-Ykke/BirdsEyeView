<?php

namespace App\Filament\Widgets\DefaultFilters;

use App\Support\Enums\TitleTypes;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Set;

class TitleTypesFilter
{
    public static function get(): Select
    {
        return Select::make('titleTypes')
            ->multiple()
            ->label('Type titels')
            ->options(function () {
                return TitleTypes::translationArray();
            })
            ->preload()
            ->maxItems(10)
            ->hintAction(
                Action::make('clearField')
                    ->label('Reset')
                    ->icon('heroicon-m-trash')
                    ->action(function (Set $set) {
                        $set('titleTypes', []);
                    })
            )
            ->native(false)
            ->searchable();
    }
}
