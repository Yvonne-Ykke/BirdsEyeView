<?php

namespace App\Filament\Widgets\Charts\DefaultChartFilters;

use Carbon\Carbon;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;

class YearTillFilter
{
    public static function get()
    {
        return TextInput::make('yearTill')
            ->label('Tot')
            ->live()
            ->minValue(0)
            ->placeholder(Carbon::now()->year)
            ->gt('yearFrom')
            ->numeric()
            ->hintAction(
                Action::make('clearField')
                    ->label('Reset')
                    ->icon('heroicon-m-trash')
                    ->action(function (Set $set) {
                        $set('yearTill', null);
                    })
            );
    }
}
