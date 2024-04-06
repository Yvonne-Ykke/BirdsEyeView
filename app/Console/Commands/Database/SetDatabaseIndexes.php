<?php

namespace App\Console\Commands\Database;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SetDatabaseIndexes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-database-indexes {--cacheCharts} {--cacheTables} {--cacheWidgets}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set database indexes for after importing data';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if ($this->checkDatabaseImporting()) {
            $this->error('Database still importing, skipping...');
            return;
        }

        $this->setTitlesIndexes();
        $this->setRatingsIndexes();

        Artisan::call('cache:clear');
        $this->info('Cleared cache');

        if ($this->option('cacheWidgets')){
            $this->cacheWidgets();
            return;
        }
        
        $this->cacheCharts();
        $this->cacheTables();
    }

    private function checkDatabaseImporting(): bool
    {
        return DB::query()
                ->from('jobs')
                ->count() > 0;
    }

    private function setTitlesIndexes(): void
    {
        Schema::table('titles', function (Blueprint $table) {
            if (!Schema::hasIndex('titles', ['budget'])) {
                $table->index('budget');
                $this->info("Set titles budget index");
            } else {
                $this->warn("Titles budget index already exists, Skipping...");
            }

            if (!Schema::hasIndex('titles', ['revenue'])) {
                $table->index('revenue');
                $this->info("Set titles revenue index");
            } else {
                $this->warn("Titles revenue index already exists, Skipping...");
            }

            if (!Schema::hasIndex('titles', ['type'])) {
                $table->index('type');
                $this->info("Set titles type index");
            } else {
                $this->warn("Titles type index already exists, Skipping...");
            }
        });
    }

    private function setRatingsIndexes(): void
    {
        Schema::table('model_has_ratings', function (Blueprint $table) {
            if (!Schema::hasIndex('model_has_ratings', ['average_rating'])) {
                $table->index('average_rating');
                $this->info("Set average_rating index");
            } else {
                $this->warn("Ratings average_rating index already exists, Skipping...");
            }

            if (!Schema::hasIndex('model_has_ratings', ['number_votes'])) {
                $table->index('number_votes');
                $this->info("Set average number_votes index");
            } else {
                $this->warn("Ratings number_votes index already exists, Skipping...");
            }
        });
    }

    private function cacheCharts(): void
    {
        if ($this->option('cacheCharts')) {
            Artisan::call('cache:widget-charts');
            $this->info('Graphs are being cached');
        }
    }

    private function cacheTables(): void
    {
        if ($this->option('cacheTables')) {
            Artisan::call('cache:widget-tables');
            $this->info('Table widgets are being cached');
        }
    }

    private function cacheWidgets(): void
    {
        Artisan::call('cache:widget-charts');
        $this->info('Graphs are being cached');

        Artisan::call('cache:widget-tables');
        $this->info('Table widgets are being cached');
    }


}
