<?php

namespace App\Filament\Widgets\Tables;

use App\Filament\Widgets\Support\TableInterface;
use App\Models\ProductionCompany;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BestProductionCompaniesTable extends Widget implements HasForms, TableInterface
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.best-production-companies-table';

    public string $title = 'Hoogst beoordeelde bedrijven';

    protected string $tableId = 'BestProductionCompaniesTable';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

            ])
            ->statePath('data');
    }

    public function create(): void
    {
        dd($this->form->getState());
    }

    public function buildQuery(array $filterValues): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
    {
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
    }

    public function getCacheKey(array $filterValues): string
    {
        return $this->tableId;
    }


    function getTableData(): array
    {
        $filters = $this->getFilterValues();
        $query = $this->buildQuery($filters);
        $cacheKey = $this->getCacheKey($filters);

        return Cache::rememberForever($cacheKey, function () use ($query) {
            return $query
                ->get()
                ->toArray();
        });
    }

    function getFilterValues(): array
    {
        return [
            // TODO: Implement filters
        ];
    }
}
