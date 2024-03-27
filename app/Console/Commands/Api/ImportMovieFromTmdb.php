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
    protected $signature = 'app:import-movies-from-tmdb {limitJobs?} {--recordsToImport=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports movies from TMDB based on the amount of titles currently in the database';

    private int $requestPerJob = 50;

    /**
     * Execute the console command.
     */
    #[NoReturn] public function handle(): void
    {
        if ($this->option('recordsToImport')) {
            if (!is_numeric($this->option('recordsToImport'))) {
                $this->error('Records to import value is not a number');
                return;
            }

            if ($this->option('recordsToImport') < 100) {
                $this->error('Cant import less than 100 records');
                return;
            }
        }
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
            if ($increment >= $this->argument('limitJobs') * $this->requestPerJob) {
                return;
            }
        }

        $tmdbExternIds = $this->setTmdbExternIds($increment, $start, $end);

        ImportTmdbMoviesJob::dispatch($tmdbExternIds);
        echo "dispatched " . count($tmdbExternIds) . " jobs\n";

        $this->dispatchJobs(
            start: $start,
            end: $end,
            increment: $increment + $this->requestPerJob
        );
    }

    private function createJobBatches(): array
    {
        $amountOfTitles = (int)$this->option('recordsToImport') ?? Title::query()->count();
        $start = 1;
        $end = $amountOfTitles;
        $batchSize = $amountOfTitles / 50;

        return $this->batchNumbers($start, $end, $batchSize);
    }

    private function batchNumbers($start, $end, $batchSize): array
    {
        $batches = [];

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

        /*If there's more then 50 requests to handle*/
        if ($end - $start + $increment >= $this->requestPerJob) {
            for ($i = 0; $i < $this->requestPerJob; $i++) {
                $tmdbExternIds[] = $i + $start + $increment;
            }
        } else {
            for ($i = $start + $increment; $i <= $end; $i++) {
                $tmdbExternIds[] = $i;
            }
        }

        return $tmdbExternIds;
    }


}
