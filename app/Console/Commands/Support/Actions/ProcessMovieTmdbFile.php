<?php

namespace App\Console\Commands\Support\Actions;

use App\Jobs\ImportTmdbMoviesJob;
use Illuminate\Support\Facades\Storage;

class ProcessMovieTmdbFile
{
    public function __invoke(string $file): void
    {
        $json = fopen(Storage::path($file), 'r');
        $i = 0;
        $batchI = 0;
        $batch = [];

        while (!feof($json)) {
            $data = json_decode(fgets($json), true);

            if (!$data) continue;

            $batch[] = $data['id'];

            if ($batchI >= 50) {
                ImportTmdbMoviesJob::dispatch($batch);
                $batchI = 0;
                $batch = [];
            }
            
            if ($i >= 1000) {
                echo "queued 1000 movies \n";
                $i = 0;
            }
            $batchI++;
            $i++;
        }

        fclose($json);
    }
}
