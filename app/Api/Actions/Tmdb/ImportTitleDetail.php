<?php

namespace App\Api\Actions\Tmdb;

use App\Api\Tmdb\TmdbApi;
use App\Models\Genre;
use App\Models\Rating;
use App\Models\Title;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ImportTitleDetail
{
    private string $endpoint = '3/movie/';
    private array|string $result = [];
    private Title $title;

    public function __invoke(int $tmbdExternId)
    {
        $this->result = app(TmdbApi::class)($this->endpoint . $tmbdExternId);

        if (is_string($this->result)) {
            Log::error($this->result);
            return;
        }

        $this->title = $this->saveTitle();
        $this->saveGenres();
        $this->saveRating();
    }

    private function saveTitle()
    {
        return Title::updateOrCreate(
            ['imdb_externid' => $this->result['imdb_id']],
            [
                'imdb_externid' => $this->result['imdb_id'],
                'tmdb_externid' => $this->result['id'],
                'primary_title' => $this->result['title'],
                'original_title' => $this->result['original_title'],
                'is_adult' => $this->result['adult'],
                'start_year' => Carbon::create($this->result['release_date'])->year,
                'budget' => $this->result['budget'],
                'revenue' => $this->result['revenue'],
                'runtime_minutes' => $this->result['runtime'],
            ]
        );
    }

    private function saveGenres(): void
    {
        foreach ($this->result['genres'] as $genre) {
            $genre = Genre::updateOrCreate(
                ['name' => $genre['name']],
                [
                    'name' => $genre['name'],
                    'tmdb_externid' => $genre['id'],
                ],
            );
            $this->title->genres()->attach($genre->id);
        }
    }

    private function saveRating(): void
    {
        Rating::updateOrCreate(
            [
                'model_type' => Title::class,
                'model_id' => $this->title->id,
            ],
            [
                'model_type' => Title::class,
                'model_id' => $this->title->id,
                'average_rating' => $this->result['vote_average'],
                'number_votes' => $this->result['vote_count']
            ]
        );
    }
}
