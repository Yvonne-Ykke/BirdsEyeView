<?php

namespace App\Console\Commands\Database;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropDatabaseIndexes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:drop-database-indexes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->dropTitlesIndexes();
        $this->dropRatingsIndexes();
    }

    private function dropTitlesIndexes(): void
    {
        Schema::table('titles', function (Blueprint $table) {
            if(Schema::hasIndex('titles', ['budget'])) {
                $table->dropIndex(['budget']);
                $this->info("Dropped titles budget index");
            } else {
                $this->warn("Titles budget index doesnt exist, Skipping...");
            }

            if(Schema::hasIndex('titles', ['revenue'])) {
                $table->dropIndex(['revenue']);
                $this->info( "Dropped titles revenue index");
            } else {
                $this->warn("Titles revenue index doesnt exist, Skipping...");
            }

            if(Schema::hasIndex('titles', ['type'])) {
                $table->dropIndex(['type']);
                $this->info( "Dropped titles type index");
            } else {
                $this->warn("Titles type index doesnt exist, Skipping...");
            }
        });
    }

    private function dropRatingsIndexes(): void
    {
        Schema::table('model_has_ratings', function (Blueprint $table) {
            if(Schema::hasIndex('model_has_ratings', ['average_rating'])) {
                $table->dropIndex(['average_rating']);
                $this->info( "Dropped average_rating index");
            } else {
                $this->warn("Ratings average_rating index doesnt exist, Skipping...");
            }

            if(Schema::hasIndex('model_has_ratings', ['number_votes'])) {
                $table->dropIndex(['number_votes']);
                $this->info( "Dropped average number_votes index");
            } else {
                $this->warn("Ratings number_votes index doesnt exist, Skipping...");
            }
        });
    }
}
