<?php

namespace App\Console\Commands;

use App\Api\Tmdb\TmdbApi;
use App\Models\Title;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\NoReturn;

class ImportMovieFromTmdb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-movies-from-tmdb {movie}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private string $endpoint = '3/movie/';
    private string $urlParameters = '?language=en-US';

    private ?Collection $data = null;

    /**
     * Execute the console command.
     */
    #[NoReturn] public function handle(): void
    {
        $this->data = collect(app(TmdbApi::class)(
            $this->endpoint .
            $this->argument('movie') .
            $this->urlParameters)
        );

        dd($this->data);
    }

    private function saveMovie() {
        return Title::create([
            'imdb_externid' => $this->data['imdb_id'],
            'tmdb_externid' => $this->data['id']
        ]);
    }


}
