<?php

namespace App\Filament\Widgets\Charts\Runtime;

use App\Filament\Widgets\DefaultFilters\GenreFilter;
use App\Filament\Widgets\DefaultFilters\TitleTypesFilter;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class RuntimeChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static string $chartId = 'runtimeChart';

    protected int|string|array $columnSpan = 2;

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Gemiddelde runtime per genre';

    protected static ?string $pollingInterval = null;

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        if (!$this->readyToLoad) {
            return [];
        }

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Gemiddelde tijdsduur',
                    'data' => $this->getChartData(),
                ],
            ],
            'xaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
                'min' => 0,
                'max' => 150,
            ],
            'colors' => ['#f59e0b'],
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            GenreFilter::get()
                ->live()
                ->afterStateUpdated(function () {
                    $this->updateOptions();
                }),
            TitleTypesFilter::get()
                ->default(['movie']),
        ];
    }

    private function getChartData(): array
    {
        $query = $this->buildQuery();
        $cacheKey = $this->getCacheKey();

        return Cache::rememberForever($cacheKey, function () use ($query) {
            return $query
                ->get()
                ->toArray();
        });
    }

    private function getCacheKey(): string
    {
        $titleTypesFilterKey = !empty($this->filterFormData['titleTypes'])
            ? implode('-', $this->filterFormData['titleTypes'])
            : '';

        $titleGenresFilterKey = !empty($this->filterFormData['genres'])
            ? implode('-', $this->filterFormData['genres'])
            : '';

        return 'getAverageRuntimeChart-'
            . '-' . $titleTypesFilterKey
            . '-' . $titleGenresFilterKey;
    }

    private function buildQuery(): Builder
    {
        $query = DB::query()
            ->selectRaw("cast(sum(runtime_minutes) / count(runtime_minutes) as decimal(16, 2)) as y, genres.name as x")
            ->from('titles')
            ->join('title_genres', 'titles.id', '=', 'title_genres.title_id')
            ->join('genres', 'title_genres.genre_id', '=', 'genres.id')
            ->where('name', '!=', '\N')
            ->where('name', '!=', 'Adult')
            ->where('name', '!=', 'undefined')
            ->orderBy('name')
            ->groupBy('genres.name');

        if (!empty($this->filterFormData['genres'])) {
            $query->whereIn('id', $this->filterFormData['genres']);
        } else {
            $query->limit(10);
        }

        if (!empty($this->filterFormData['titleTypes'])) {
            $query->whereIn('type', $this->filterFormData['titleTypes']);
        }

        return $query;
    }
}
