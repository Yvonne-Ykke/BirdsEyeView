<?php

namespace App\Filament\Widgets\Tables\Actors;

use App\Filament\Widgets\Charts\DefaultChartFilters\GenreFilter;
use App\Filament\Widgets\Support\TableInterface;
use App\Models\People;
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

    public string $title = 'Meest winstgevende acteurs';

    protected string $tableId = 'BestActorsTable';

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
        $query = People::query()
        ->select('people.name', DB::raw('SUM(titles.revenue - titles.budget) as total_profit'))
        ->from('people')
        ->join('people_professions', 'people.id', '=', 'people_professions.people_id')
        ->join('professions', 'people_professions.profession_id', '=', 'professions.id')
        ->join('model_has_crew', 'people.id', '=', 'model_has_crew.people_id')
        ->join('titles', 'model_has_crew.model_id', '=', 'titles.id')
        ->where('professions.name', '=', 'actor')
        ->where('titles.revenue', '>', 0)
        ->where('titles.budget', '>', 0)
        ->groupBy('people.id', 'people.name')
        ->orderByDesc('total_profit')
        ->limit(10);

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
