<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Genre extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'tmdb_externid',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [

    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [

    ];

    public function titles(): BelongsToMany
    {
        return $this->belongsToMany(Title::class, 'title_genres', 'genre_id', 'title_id');
    }

    public function getAverageRating(int $minimalAmountReviews, int $maxAmountReviews, array $titleTypes = []): array
    {
        $query = DB::query()
            ->selectRaw("cast(sum(average_rating * number_votes) / sum(number_votes) as decimal(16, 2)) as genre_average_rating, sum(number_votes) as sum_votes")
            ->from('titles')
            ->join('model_has_ratings', 'titles.id', '=', 'model_has_ratings.model_id')
            ->join('title_genres', 'titles.id', '=', 'title_genres.title_id')
            ->where('model_has_ratings.model_type', Title::class)
            ->where('title_genres.genre_id', $this->id)
            ->where('model_has_ratings.number_votes', '>=', $minimalAmountReviews)
            ->where('model_has_ratings.number_votes', '<=', $maxAmountReviews);

        if (!empty($titleTypes)) {
            $query->whereIn('type', $titleTypes);
        }

        $titleTypesFilterKey = !empty($titleTypes)
            ? implode('-', $titleTypes)
            : '';

        $cacheKey = 'genreGetAverageRating-' . $minimalAmountReviews . '-' . $maxAmountReviews . '-' . $titleTypesFilterKey;

        $result = Cache::rememberForever($cacheKey, function () use ($query) {
            return $query
                ->get()
                ->toArray()[0];
        });

        return [
            'averageRating' => $result->genre_average_rating,
            'numberVotes' => $result->sum_votes
        ];
    }

    public function getAverageRuntime(array $titleTypes = []): array
    {
        $query = DB::query()
            ->selectRaw("cast(sum(runtime_minutes) / count(runtime_minutes) as decimal(16, 2)) as genre_average_runtime")
            ->from('titles')
            ->join('title_genres', 'titles.id', '=', 'title_genres.title_id')
            ->where('title_genres.genre_id', $this->id);

        if (!empty($titleTypes)) {
            $query->whereIn('type', $titleTypes);
        }

        $result = $query
            ->get()
            ->toArray()[0];

        return [
            'averageRuntime' => $result->genre_average_runtime,
        ];
    }

    public function getRuntimeRating(array $titleTypes = []): array
    {
        $query = DB::query()
            ->selectRaw("cast(sum(average_rating * number_votes) / sum(number_votes) as decimal(16, 2)) as y, (runtime_minutes / 5) * 5 AS x")
            ->from('titles')
            ->join('model_has_ratings', 'titles.id', '=', 'model_has_ratings.model_id')
            ->join('title_genres', 'titles.id', '=', 'title_genres.title_id')
            ->where('title_genres.genre_id', $this->id)
            ->whereNotNull('number_votes')
            ->where('number_votes', '>', 0)
            ->where('runtime_minutes', '<', 500)
            ->whereNotNull('runtime_minutes')
            ->groupBy(DB::raw('(runtime_minutes / 5) * 5'));

        if (!empty($titleTypes)) {
            $query->whereIn('type', $titleTypes);
        }

        $results = $query
           ->get()
           ->toArray(); 

        return $results;
    }
}
