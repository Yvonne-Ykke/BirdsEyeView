<?php

namespace App\Filament\Widgets\Support;
use Illuminate\Database\Query\Builder;

interface ChartInterface
{
    function getChartData(): array;

    function getCacheKey(array $filterValues): string;

    function buildQuery(array $filterValues): Builder;

    function getFilterValues(): array;
}
