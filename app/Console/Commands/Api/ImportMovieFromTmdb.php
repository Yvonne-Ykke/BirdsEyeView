<?php

namespace App\Console\Commands\Api;

use App\Jobs\ImportTmdbMoviesJob;
use App\Models\Title;
use Illuminate\Console\Command;
use JetBrains\PhpStorm\NoReturn;

class ImportMovieFromTmdb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-movies-from-tmdb {limitJobs?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports movies from TMDB based on the amount of titles currently in the database';


    /**
     * Execute the console command.
     */
    #[NoReturn] public function handle(): void
    {
        $jobBatches = $this->createJobBatches();

        foreach ($jobBatches as $batch) {
            $this->dispatchJobs($batch['start'], $batch['end'], 0);
        }
    }

    private function dispatchJobs(int $start, int $end, int $increment): void
    {
        $incrementedStart = $increment + $start;

        if ($incrementedStart >= $end) {
            return;
        }

        if ($this->argument('limitJobs')) {
            if ($increment >= $this->argument('limitJobs') * 50) {
                return;
            }
        }

        $tmdbExternIds = $this->setTmdbExternIds($increment, $start, $end);

        ImportTmdbMoviesJob::dispatch($tmdbExternIds);
        echo "dispatched " . count($tmdbExternIds) . " jobs\n";

        $this->dispatchJobs($start, $end, $increment + 50);
    }

    private function createJobBatches(): array
    {
        $amountOfTitles = Title::query()->count();
        $start = 1;
        $end = $amountOfTitles;
        $batchSize = $amountOfTitles / 50;

        return $this->batchNumbers($start, $end, $batchSize);
    }

    private function batchNumbers($start, $end, $batchSize): array
    {
        $batches = array();

        for ($i = $start; $i <= $end; $i += $batchSize) {
            $batch = array(
                'start' => (int)floor($i),
                'end' => (int)floor(min($i + $batchSize - 1, $end))
            );

            $batches[] = $batch;
        }

        return $batches;
    }

    private function setTmdbExternIds(int $increment, int $start, int $end): array
    {
        $tmdbExternIds = [];

        if ($end - $start + $increment >= 50) {
            $numberToReach  = $increment + 50;
            for ($i = $increment + 1; $i <= $numberToReach; $i++) {
                $tmdbExternIds[] = $i;
            }

        } else {
            for ($i = 1; $i <= $end; $i++) {
                $tmdbExternIds[] = $i;
            }
        }

        return $tmdbExternIds;
    }


}
