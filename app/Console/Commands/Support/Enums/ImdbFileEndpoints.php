<?php

namespace App\Console\Commands\Support\Enums;

use App\Support\Enums\BaseEnumTrait;

enum ImdbFileEndpoints: string
{
    use BaseEnumTrait;

    case NAME_BASICS = "name.basics.tsv.gz";
    case TITLE_AKAS = "title.akas.tsv.gz";
    case TITLE_BASICS = "title.basics.tsv.gz";
    case TITLE_CREW = "title.crew.tsv.gz";
    case TITLE_EPISODE = "title.episode.tsv.gz";
    case TITLE_PRINCIPALS = "title.principals.tsv.gz";
    case TITLE_RATINGS = "title.ratings.tsv.gz";
}


