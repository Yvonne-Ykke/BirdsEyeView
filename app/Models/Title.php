<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Title extends Model
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
        'imdb_externid',
        'primary_title',
        'original_title',
        'type',
        'is_adult',
        'start_year',
        'end_year',
        'budget',
        'revenue',
        'runtime_minutes',
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

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'title_genres', 'title_id', 'genre_id');
    }

    public function rating(): MorphOne
    {
        return $this->morphOne(Rating::class, 'rateable', 'model_type', 'model_id');
    }


    public function crew(): MorphToMany
    {
        return $this->morphToMany(People::class, 'model', 'model_has_crew');
    }
}
