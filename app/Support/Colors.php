<?php

namespace App\Support;

enum Colors: string
{
    case RED = '#dc2626';
    case NEUTRAL = '#525252';
    case ORANGE = '#ea580c';
    case AMBER = '#d97706';
    case YELLOW = '#ca8a04';
    case LIME = '#65a30d';
    case GREEN = '#16a34a';
    case TEAL = '#0d9488';
    case INDIGO = '#4f46e5';
    case VIOLET = '#7c3aed';
    case FUCHSIA = '#c026d3';
    case PINK = '#db2777';
    case ROSE = '#e11d48';

    public static function getRandom($amount = 1): int|array|string
    {
        $colors = array_rand(self::array(), $amount);
        if (is_string($colors))
            return [$colors];

        return $colors;
    }

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

