<?php

namespace App\Filament\Widgets\Charts\DefaultChartFilters;

use App\Models\ProductionCompany;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Set;

class ProductionCompaniesFilter
{
    public static function get(int $minimalAmountMadeProductions = 20): Select
    {
        return Select::make('productionCompanies')
            ->multiple()
            ->label('Toon enkel')
            ->options(function (Get $get) use ($minimalAmountMadeProductions) {
                return ProductionCompany::query()
                    ->join('model_has_production_company as mhpc', 'production_companies.id', '=', 'mhpc.production_company_id')
                    ->join('titles', 'mhpc.model_id', '=', 'titles.id')
                    ->where('revenue', '>', 0)
                    ->where('budget', '>', 0)
                    ->groupBy('production_companies.id')
                    ->groupBy(['production_companies.name', 'production_companies.id'])
                    ->havingRaw('COUNT(DISTINCT mhpc.id) > ' . max($get('minimalAmountOfProductions'), $minimalAmountMadeProductions))
                    ->pluck('production_companies.name', 'production_companies.id');
            })
            ->hintAction(
                Action::make('clearField')
                    ->label('Reset')
                    ->icon('heroicon-m-trash')
                    ->action(function (Set $set) {
                        $set('productionCompanies', []);
                    })
            )
            ->helperText('Staat er een bedrijf niet tussen? Het kan zijn dat we daar geen financiele gegevens over hebben')
            ->native(false)
            ->searchable();
    }
}
