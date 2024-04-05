<?php

namespace App\Filament\Widgets\Support;

interface WidgetInterface
{
    function buildQuery(array $filterValues): \Illuminate\Database\Query\Builder | \Illuminate\Database\Eloquent\Builder;

    function getCacheKey(array $filterValues): string;

    function getFilterValues(): array;
}
