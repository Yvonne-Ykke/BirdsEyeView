<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        return $this->belongsToMany(Title::class, 'title_genres', 'genre_id', 'id');
    }

    public function getAverageRating(int $minimalAmountReviews, int $maxAmountReviews): array
    {
       $result =  DB::select(
           "SELECT
                       cast(sum(average_rating) / count(average_rating) as decimal(16, 2)) as genre_average_rating,
                       sum(number_votes) as sum_votes
                  FROM titles
                         INNER JOIN model_has_ratings
                                    on titles.id = model_has_ratings.model_id
                                        and model_has_ratings.model_type = 'App\Models\Title'
                         INNER JOIN public.title_genres tg on titles.id = tg.title_id
                  WHERE tg.genre_id = (SELECT id from genres where id = $this->id)
                  AND model_has_ratings.number_votes >= $minimalAmountReviews
                  AND model_has_ratings.number_votes <= $maxAmountReviews"
        )[0];

        return [
            'averageRating' => $result->genre_average_rating,
            'numberVotes' => $result->sum_votes
        ];
    }
}
