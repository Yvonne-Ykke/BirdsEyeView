<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class ProductionCompany extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tmdb_externid',
        'imdb_externid',
        'name',
        'origin_country',
    ];

    protected $table = 'production_companies';

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

    public function titles(): MorphToMany
    {
        return $this->morphedByMany(
            related: Title::class,
            name: 'model',
            table: 'model_has_production_company',
            foreignPivotKey: 'production_company_id',
            relatedPivotKey: 'model_id'
        );
    }
}
