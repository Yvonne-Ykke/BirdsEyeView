<?php

namespace App\Console\Commands\Support;

use App\Filament\Widgets\Support\Actions\GetChartInterfaceImplementationObjects;
use App\Filament\Widgets\Support\ChartInterface;
use App\Jobs\CacheQueryJob;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class CacheCharts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:charts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected Collection $charts;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->charts = collect(app(GetChartInterfaceImplementationObjects::class)());

        $this->charts->each(function (ChartInterface $item) {
            $filters = $item->getFilterValues();
            $query = $item->buildQuery($filters);
            $cacheKey = $item->getCacheKey($filters);

            CacheQueryJob::dispatch(collect($query), $cacheKey);
            $this->handleFilters($filters);
        });

    }

    private function handleFilters(array $filters)
    {
        //TODO IMPLEMENT
    }


}
