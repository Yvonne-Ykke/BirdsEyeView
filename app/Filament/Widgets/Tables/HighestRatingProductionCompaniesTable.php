<?php

namespace App\Filament\Widgets\Tables;

use App\Filament\Widgets\Tables\DefaultFilters\GenreFilter;
use App\Models\ProductionCompany;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\Action;

class HighestRatingProductionCompaniesTable extends BaseWidget
{


    protected static ?string $heading = 'Bedrijven met hoogste gemiddelde recensies';

    public function table(Table $table): Table
    {
        set_time_limit(600);
        return $table
            ->query(function () {
                return ProductionCompany::select([
                    'production_companies.name as name',
                    DB::raw('CAST(SUM(mhr.average_rating * mhr.number_votes) / SUM(mhr.number_votes) AS DECIMAL(16, 2)) AS true_average_rating'),
                    DB::raw('COUNT(DISTINCT t.id) AS made_titles')
                ])
                    ->join('model_has_production_company as mhpc', 'production_companies.id', '=', 'mhpc.production_company_id')
                    ->join('titles as t', 'mhpc.model_id', '=', 't.id')
                    ->join('model_has_ratings as mhr', 't.id', '=', 'mhr.model_id')
                    ->join('title_genres as tg', 't.id', '=', 'tg.title_id')
                    ->join('genres as g', 'tg.genre_id', '=', 'g.id')
                    ->where('mhr.number_votes', '>', 0)
                    ->where('mhr.average_rating', '>', 0)
                    ->groupBy(['production_companies.name', 'production_companies.id'])
                    ->havingRaw('COUNT(DISTINCT t.id) > 100')
                    ->orderByDesc('true_average_rating')
                    ->orderByDesc('made_titles');
            })
            ->columns([
                TextColumn::make('name')
                    ->label('Naam'),
                TextColumn::make('true_average_rating')
                    ->label('Gemiddelde recensie'),
                TextColumn::make('made_titles')
                    ->label('Aantal gemaakte titels'),
            ])
            ->filters([
                GenreFilter::get()
                    ->name('g.id')
                    ->default([195 => 'Action'])
            ]);
    }

    public function getTableRecordKey(Model $record): string
    {
        return uniqid();
    }

}
