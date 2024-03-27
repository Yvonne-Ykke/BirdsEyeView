<?php

namespace App\Console\Commands\Database;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetDatabaseIndexes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-database-indexes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set useful database indexes for after importing data';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->setTitlesIndexes();
        $this->setRatingsIndexes();
    }

    private function setTitlesIndexes(): void
    {
        Schema::table('titles', function (Blueprint $table) {
            if(!Schema::hasIndex('titles', ['budget'])) {
                $table->bigInteger('budget')->index();
                $this->info("Set titles budget index");
            } else {
                $this->warn("Titles budget index already exists, Skipping...");
            }

            if(!Schema::hasIndex('titles', ['revenue'])) {
                $table->bigInteger('revenue')->index();
                $this->info( "Set titles revenue index");
            } else {
                $this->warn("Titles revenue index already exists, Skipping...");
            }

            if(!Schema::hasIndex('titles', ['type'])) {
                $table->string('type')->index();
                $this->info( "Set titles type index");
            } else {
                $this->warn("Titles type index already exists, Skipping...");
            }
        });
    }

    private function setRatingsIndexes(): void
    {
        Schema::table('model_has_ratings', function (Blueprint $table) {
            if(!Schema::hasIndex('model_has_ratings', ['average_rating'])) {
                $table->float('average_rating')->index();
                $this->info( "Set average_rating index");
            } else {
                $this->warn("Ratings average_rating index already exists, Skipping...");
            }

            if(!Schema::hasIndex('model_has_ratings', ['number_votes'])) {
                $table->integer('number_votes')->index();
                $this->info( "Set average number_votes index");
            } else {
                $this->warn("Ratings number_votes index already exists, Skipping...");
            }
        });
    }
}
