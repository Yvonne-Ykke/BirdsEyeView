<?php

namespace App\Filament\Widgets\Tables\Actors;

use App\Filament\Widgets\Charts\DefaultChartFilters\GenreFilter;
use App\Filament\Widgets\Support\TableInterface;
use App\Models\ProductionCompany;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BestActorsTableWidget extends Widget implements HasForms, TableInterface
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.best-actors-table-widget';

    public string $title = 'Hoogst beoordeelde acteurs';

    protected string $tableId = 'BestProductionCompaniesTable';

    /*Data uit formulier*/
    public array $data = [];

    /*Data die in tabel gestopt wordt*/
    public array $tableData = [];

    public function mount(): void
    {
        set_time_limit(600);

        $this->form->fill();
        $this->tableData = $this->getTableData();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                GenreFilter::get()
                    ->multiple(false)
                    ->label('genre'),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $this->tableData = $this->getTableData();
    }

    public function buildQuery(array $filterValues): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
    {
        $query = ProductionCompany::query()
            ->select([
                'production_companies.name as name',
                DB::raw('CAST(SUM(mhr.average_rating * mhr.number_votes) / SUM(mhr.number_votes) AS DECIMAL(16, 2)) AS true_average_rating'),
                DB::raw('COUNT(DISTINCT t.id) AS made_titles')
            ])
            ->join('model_has_production_company as mhpc', 'production_companies.id', '=', 'mhpc.production_company_id')
            ->join('titles as t', 'mhpc.model_id', '=', 't.id')
            ->join('model_has_ratings as mhr', 't.id', '=', 'mhr.model_id')
            ->where('mhr.number_votes', '>', 0)
            ->where('mhr.average_rating', '>', 0)
            ->groupBy(['production_companies.name', 'production_companies.id'])
            ->havingRaw('COUNT(DISTINCT t.id) > 100')
            ->limit(10)
            ->orderByDesc('true_average_rating')
            ->orderByDesc('made_titles');

        if ($filterValues['genreId']) {
            $query
                ->join('title_genres as tg', 't.id', '=', 'tg.title_id')
                ->where('tg.genre_id', (int)$filterValues['genreId']);

        }

        return $query;
    }

    public function getCacheKey(array $filterValues): string
    {
        return $this->tableId
            . '-' . $filterValues['genreId'];
    }


    function getTableData(): array
    {
        $filters = $this->getFilterValues();
        $query = $this->buildQuery($filters);
        $cacheKey = $this->getCacheKey($filters);

        set_time_limit(600);
        return Cache::rememberForever($cacheKey, function () use ($query) {
            return $query
                ->get()
                ->toArray();
        });
    }

    function getFilterValues(): array
    {
        return [
            'genreId' => $this->form->getState()['genres'] ?? null
        ];
    }
}
