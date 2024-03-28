<?php

namespace App\Filament\Widgets\DefaultFilters;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;

class MinimumAmountReviewsFilter
{
    public static function get(): TextInput
    {
        return TextInput::make('minimumAmountReviews')
            ->label('Minimaal aantal reviews')
            ->default(0)
            ->required()
            ->numeric()
            ->minValue(0)
            ->hintAction(
                Action::make('clearField')
                    ->label('Reset')
                    ->icon('heroicon-m-trash')
                    ->action(function (Set $set) {
                        $set('minimumAmountReviews', 0);
                    })
            );
    }
}
