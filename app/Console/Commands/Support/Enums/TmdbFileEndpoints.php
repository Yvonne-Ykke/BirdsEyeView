<?php

namespace App\Console\Commands\Support\Enums;

use App\Support\Enums\BaseEnumTrait;

enum TmdbFileEndpoints: string
{
    use BaseEnumTrait;

    case MOVIES = 'movie_ids_';
    case PRODUCTION_COMPANIES = 'production_company_ids_';
}
