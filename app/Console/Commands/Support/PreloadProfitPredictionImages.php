<?php

namespace App\Console\Commands\Support;

use App\Jobs\SaveGenreProfitPredictionImagesJob;
use App\Models\Genre;
use App\Support\Actions\FindOrCreateStorageDirectory;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class PreloadProfitPredictionImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:preload-profit-prediction-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create all the images for genre profit prediction';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        app(FindOrCreateStorageDirectory::class)('public/r');

        $genres = $this->getGenres();
        $currentYear = now()->year;
        set_time_limit(1800);

        foreach ($genres as $genre) {
            for ($i = 1899; $i <= $currentYear; $i++) {
//                SaveGenreProfitPredictionImagesJob::dispatch($genre->id, $i);
                $this->saveImage($genre->id, $i);
                $this->info('saved year ' . $i . ' for genre ' . $genre->id);
            }
        }
    }

    private function getGenres(): \Illuminate\Database\Eloquent\Collection|array
    {
        return Genre::query()
            ->where('name', '!=', '\N')
            ->where('name', '!=', 'Adult')
            ->get();
    }

    public function saveImage(int $genreId, int $year): void
    {
        $path = storage_path('app/public/r');
        $image = "profit_over_time-" . $genreId . "-" . $year . ".png";

        if (Storage::exists('public/r/' . $image)) {
            return;
        }

        $process = new Process([
            'Rscript',
            base_path('scripts/R/genre-predictions.R'),
            $path,
            $genreId,
            $year,
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception($process->getErrorOutput());
        }
    }


}
