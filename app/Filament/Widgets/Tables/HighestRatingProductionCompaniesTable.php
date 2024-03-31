<?php

namespace App\Filament\Widgets\Tables;

use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class HighestRatingProductionCompaniesTable extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                // ...
            )
            ->columns([
                // ...
            ]);
    }
}
