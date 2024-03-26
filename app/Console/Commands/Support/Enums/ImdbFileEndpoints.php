<?php

namespace App\Console\Commands\Support\Enums;

enum ImdbFileEndpoints: string
{
    case NAME_BASICS = "name.basics.tsv.gz";
    case TITLE_AKAS = "title.akas.tsv.gz";
    case TITLE_BASICS = "title.basics.tsv.gz";
    case TITLE_CREW = "title.crew.tsv.gz";
    case TITLE_EPISODE = "title.episode.tsv.gz";
    case TITLE_PRINCIPALS = "title.principals.tsv.gz";
    case TITLE_RATINGS = "title.ratings.tsv.gz";

    public static function array(): array
    {
        return array_combine(self::values(), self::names());
    }

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

}


