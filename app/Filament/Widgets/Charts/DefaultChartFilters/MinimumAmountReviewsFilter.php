<?php

namespace App\Filament\Widgets\Charts\DefaultChartFilters;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;

class MinimumAmountReviewsFilter
{
    public static function get(): TextInput
    {
        return TextInput::make('minimumAmountReviews')
            ->label('Minimaal aantal reviews')
            ->default(1)
            ->required()
            ->lt('maxAmountReviews')
            ->numeric()
            ->minValue(1)
            ->hintAction(
                Action::make('clearField')
                    ->label('Reset')
                    ->icon('heroicon-m-trash')
                    ->action(function (Set $set) {
                        $set('minimumAmountReviews', 1);
                    })
            );
    }
}
