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

    public static function translationArray(): array
    {
        return [
            'movie' => 'Film',
            'tvMovie' => 'Tv film',
            'short' => 'Short',
            'tvMiniSeries' => 'Tv short',
            'tvSeries' => 'Serie',
            'tvShort' => 'TV mini-serie',
            'tvSpecial' => 'TV special',
        ];
    }

    public function translation(): string
    {
        return match ($this) {
            TitleTypes::MOVIE => 'Film',
            TitleTypes::TV_MOVIE => 'Tv film',
            TitleTypes::SHORT => 'Short',
            TitleTypes::TV_SHORT => 'Tv short',
            TitleTypes::TV_SERIES => 'Serie',
            TitleTypes::TV_MINI_SERIES => 'TV mini-serie',
            TitleTypes::TV_SPECIAL => 'TV special',
        };
    }


}

