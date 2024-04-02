<?php

namespace App\Filament\Widgets\Charts\Genres;

use App\Filament\Widgets\DefaultFilters\GenreFilter;
use App\Filament\Widgets\DefaultFilters\TitleTypesFilter;
use App\Filament\Widgets\Support\ChartInterface;
use App\Models\Genre;
use App\Models\Rating;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class AmountOfTitlesPerGenreChart extends ApexChartWidget implements ChartInterface
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static string $chartId = 'AmountOfTitlesPerGenre';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Aantal titels per genre';

    protected static ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 2;

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
                    'name' => 'Aantal titels',
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
            ],
            'colors' => ['#f59e0b'],
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            GenreFilter::get()
                ->maxItems(15)
                ->afterStateUpdated(function () {
                    $this->updateOptions();
                }),
            TitleTypesFilter::get()
                ->afterStateUpdated(function () {
                    $this->updateOptions();
                }),
        ];
    }

    protected function getLoadingIndicator(): null|string|View
    {
        return view('components.loading-icons.ball-clip-rotate-multiple');
    }

    public function getChartData(): array
    {
        $filters = $this->getFilterValues();
        $query = $this->buildQuery($filters);
        $cacheKey = $this->getCacheKey($filters);

        return Cache::rememberForever($cacheKey, function () use ($query) {
            return $query
                ->get()
                ->toArray();
        });
    }

    function buildQuery(array $filterValues): Builder
    {
        $query = Genre::query()
            ->selectRaw('count(title_id) as y, genres.name as x')
            ->join('title_genres', 'genres.id', 'title_genres.genre_id')
            ->where('name', '!=', '\N')
            ->where('name', '!=', 'Adult')
            ->where('name', '!=', 'undefined')
            ->orderByDesc('y')
            ->groupBy(['genre_id', 'name']);

        if (!empty($filterValues['genres'])) {
            $query->whereIn('genres.id', $filterValues['genres']);
        }

        if (!empty($filterValues['titleTypes'])) {
            $query->join('titles', 'title_genres.title_id', 'titles.id')
                ->whereIn('titles.type', $filterValues['titleTypes']);
        }

        return $query;
    }

    function getCacheKey(array $filterValues): string
    {
        $titleGenreFilterKey = !empty($filterValues['genres'])
            ? implode('-', $filterValues['genres'])
            : '';

        $titleTypesFilterKey = !empty($filterValues['titleTypes'])
            ? implode('-', $filterValues['titleTypes'])
            : '';

        return 'AmountOfTitlesPerGenreChart'
            . '-' . $titleGenreFilterKey
            . '-' . $titleTypesFilterKey;
    }

    function getFilterValues(): array
    {
        return [
            'genres' => $this->filterFormData['genres'] ?? null,
            'titleTypes' => $this->filterFormData['titleTypes'] ?? null,
        ];
    }
}
