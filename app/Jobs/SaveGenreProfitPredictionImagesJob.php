<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class SaveGenreProfitPredictionImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $genreId;
    private int $year;

    /**
     * Create a new job instance.
     */
    public function __construct(int $genreId, int $year)
    {
        $this->genreId = $genreId;
        $this->year = $year;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
//        dd($this->genreId, $this->year);
        $path = storage_path('app/public/r');
        $image = "profit_over_time-" . $this->genreId . "-" . $this->year . ".png";

        if (Storage::exists('public/r/' . $image)) {
            return;
        }

        $process = new Process([
            'Rscript',
            base_path('scripts/R/genre-predictions.R'),
            $path,
            $this->genreId,
            $this->year,
        ]);

        $process->run();


    }
}
