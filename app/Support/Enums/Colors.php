<?php

namespace App\Support\Enums;

enum Colors: string
{
    use BaseEnumTrait;

    case TEAL = '#0d9488';
    case AMBER = '#d97706';
    case VIOLET = '#7c3aed';
    case LIME = '#65a30d';
    case ROSE = '#e11d48';
    case INDIGO = '#4f46e5';

    case YELLOW = '#ca8a04';
    case FUCHSIA = '#c026d3';
    case PINK = '#db2777';
    case RED = '#dc2626';

    case ORANGE = '#ea580c';
    case GREEN = '#16a34a';
    case NEUTRAL = '#525252';


    public static function getRandom($amount = 1): int|array|string
    {
        $colors = array_rand(self::array(), $amount);
        if (is_string($colors))
            return [$colors];

        return $colors;
    }

    public static function getStatic($amount = 1): array
    {
        return array_slice(self::values(), 0, $amount);
    }
}

