<?php

namespace App\Filament\Widgets\DefaultFilters;

use App\Models\Rating;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;

class MaximumAmountReviewsFilter
{
    public static function get(): TextInput
    {
        return TextInput::make('maxAmountReviews')
            ->label('Maximaal aantal reviews')
            ->default(Rating::query()->max('number_votes'))
            ->required()
            ->minValue(0)
            ->numeric()
            ->hintAction(
                Action::make('clearField')
                    ->label('Reset')
                    ->icon('heroicon-m-trash')
                    ->action(function (Set $set) {
                        $set('maxAmountReviews', Rating::query()->max('number_votes'));
                    })
            );
    }
}
