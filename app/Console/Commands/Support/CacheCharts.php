<?php

namespace App\Console\Commands\Support;

use App\Filament\Widgets\Support\Actions\GetChartInterfaceImplementations;
use App\Filament\Widgets\Support\ChartInterface;
use Illuminate\Console\Command;

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

    protected array $charts;


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->charts = app(GetChartInterfaceImplementations::class)();
        dd($this->charts);
        dd($this->charts);
    }
}
