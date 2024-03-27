<?php

namespace App\Support\Enums;

enum TitleTypes: string
{
    use BaseEnumTrait;
    case MOVIE = 'movie';
    case TV_MOVIE = 'tvMovie';
    case SHORT = 'short';
    case TV_MINI_SERIES = 'tvMiniSeries';
    case TV_SERIES = 'tvSeries';
    case TV_SHORT = 'tvShort';
    case TV_SPECIAL = 'tvSpecial';

    public static function translations() {

    }

}

