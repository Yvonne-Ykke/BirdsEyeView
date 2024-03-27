<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Rating extends Model
{
    use HasFactory;

    protected $table = 'model_has_ratings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'model_type',
        'model_id',
        'average_rating',
        'number_votes',
    ];

    public function ratings(): BelongsToMany
    {
        return $this->belongsToMany(Title::class, 'title_genres', 'genre_id', 'title_id');
    }
}
