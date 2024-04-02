<?php

namespace App\Filament\Widgets\Support;
use Illuminate\Database\Query\Builder;

interface ChartInterface
{

    function getChartData(): array;

    function getCacheKey(): string;

    function buildQuery(): Builder;
}
