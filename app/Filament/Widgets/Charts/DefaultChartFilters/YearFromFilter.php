<?php

namespace App\Filament\Widgets\Charts\DefaultChartFilters;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;

class YearFromFilter
{
    public static function get(): TextInput
    {
        return TextInput::make('yearFrom')
            ->label('Vanaf')
            ->live()
            ->default(1950)
            ->required()
            ->numeric()
            ->minValue(0)
            ->hintAction(
                Action::make('clearField')
                    ->label('Reset')
                    ->icon('heroicon-m-trash')
                    ->action(function (Set $set) {
                        $set('yearFrom', 1950);
                    })
            );
    }
}
