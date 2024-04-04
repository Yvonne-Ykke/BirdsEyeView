<?php

namespace App\Filament\Widgets\Charts\DefaultChartFilters;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;

class MinimalAmountOfProductionsFilter
{
    public static function get(): TextInput
    {
        return TextInput::make('minimalAmountOfProductions')
            ->label('Minimaal aantal gemaakte producties')
            ->live(debounce: 500)
            ->default(20)
            ->required()
            ->numeric()
            ->minValue(1)
            ->hintAction(
                Action::make('clearField')
                    ->label('Reset')
                    ->icon('heroicon-m-trash')
                    ->action(function (Set $set) {
                        $set('minimalAmountOfProductions', 1950);
                    })
            );
    }
}
