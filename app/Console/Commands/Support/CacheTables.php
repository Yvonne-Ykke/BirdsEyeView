<?php

namespace App\Console\Commands\Support;

use App\Filament\Widgets\Support\Actions\GetChartInterfaceImplementationObjects;
use App\Filament\Widgets\Support\Actions\GetTableInterfaceImplementationObjects;
use App\Filament\Widgets\Support\ChartInterface;
use App\Filament\Widgets\Support\TableInterface;
use App\Jobs\CacheQueryJob;
use App\Models\Genre;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class CacheTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:widget-tables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache all widget tables';
    private Collection $tables;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->tables = collect(app(GetTableInterfaceImplementationObjects::class)());

        $this->tables->each(function (TableInterface $item) {
            $this->dispatchJob($item);

            $filters = $item->getFilterValues();
            $this->handleFilters($filters, $item);
        });
    }

    private function dispatchJob(TableInterface $item, ?array $filters = null): void
    {
        if (!$filters) {
            $filters = $item->getFilterValues();
        }

        $query = $item->buildQuery($filters);
        $cacheKey = $item->getCacheKey($filters);

        CacheQueryJob::dispatch(collect($query), $cacheKey);
    }
    private function handleFilters(array $filters, TableInterface $item): void
    {
        $filtersKeys = array_keys($filters);

        if ($filtersKeys[array_search('genreId', $filtersKeys)]) {
            $this->handleGenreId($item);
        }
    }

    private function handleGenreId(TableInterface $item): void
    {
        $genres = Genre::query()->whereNotIn('name', ['\n', 'undefined', 'adult'])->get();
        $filters = $item->getFilterValues();

        foreach ($genres as $genre) {
            $filters['genreId'] = $genre->id;
            $this->dispatchJob($item, $filters);
        }
    }
}
