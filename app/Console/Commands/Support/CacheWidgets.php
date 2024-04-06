<?php

namespace App\Console\Commands\Support;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CacheWidgets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:widgets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache all widgets';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Artisan::call('cache:widget-charts');
        $this->info('Cached charts');

        Artisan::call('cache:widget-tables');
        $this->info('Cached tables');
    }
}
