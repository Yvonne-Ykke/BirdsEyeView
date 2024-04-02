<?php

namespace App\Filament\Widgets\Charts\Genres;

use App\Filament\Widgets\DefaultFilters\GenreFilter;
use App\Filament\Widgets\DefaultFilters\MaximumAmountReviewsFilter;
use App\Filament\Widgets\DefaultFilters\MinimumAmountReviewsFilter;
use App\Filament\Widgets\DefaultFilters\SortFilter;
use App\Filament\Widgets\DefaultFilters\TitleTypesFilter;
use App\Filament\Widgets\Support\ChartInterface;
use App\Models\Rating;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class GenreRatingsChart extends ApexChartWidget implements ChartInterface
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static string $chartId = 'genreRatingsChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Gemiddelde recensie per genre';

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
                    'name' => 'Gemiddelde score',
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
                'max' => 10,
            ],
            'colors' => ['#f59e0b'],
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            SortFilter::get(),
            GenreFilter::get()->maxItems(10),
            TitleTypesFilter::get(),
            MinimumAmountReviewsFilter::get(),
            MaximumAmountReviewsFilter::get()
                ->helperText('Door grote hoeveelheid te verwerken data kunnen deze filters traag zijn (10-15s)'),
        ];
    }

    protected function getLoadingIndicator(): null|string|View
    {
        return view('components.loading-icons.ball-clip-rotate-multiple');
    }

    function buildQuery(array $filterValues): Builder
    {
        $query = DB::query()
            ->selectRaw("cast(sum(average_rating * number_votes) / sum(number_votes) as decimal(16, 2)) as y, genres.name as x")
            ->from('titles')
            ->join('model_has_ratings', 'titles.id', '=', 'model_has_ratings.model_id')
            ->join('title_genres', 'titles.id', '=', 'title_genres.title_id')
            ->join('genres', 'title_genres.genre_id', '=', 'genres.id')
            ->where('model_has_ratings.number_votes', '>=', (int)$filterValues['minimumAmountReviews'],)
            ->where('model_has_ratings.number_votes', '<=', (int)$filterValues['maxAmountReviews'])
            ->where('genres.name', '!=', '\n')
            ->where('genres.name', '!=', 'Adult')
            ->where('genres.name', '!=', 'undefined')
            ->groupBy('genres.name');

        if (!empty($filterValues['genres'])) {
            $query->whereIn('genres.id', $filterValues['genres']);
        } else {
            $query->limit(10);
        }

        if (!empty($filterValues['titleTypes'])) {
            $query->whereIn('titles.type', $filterValues['titleTypes']);
        }

        if ($filterValues['sort']) {
            $query->orderByDesc('y');
        } else {
            $query->orderBy('y');
        }

        return $query;
    }

    public function getCacheKey(array $filterValues): string
    {
        $titleGenresFilterKey = !empty($filterValues['genres'])
            ? implode('-', $filterValues['genres'])
            : '';

        $titleTypesFilterKey = !empty($filterValues['titleTypes'])
            ? implode('-', $filterValues['titleTypes'])
            : '';

        return 'genreGetAverageRating-'
            . $filterValues['minimumAmountReviews']
            . '-' . $filterValues['maxAmountReviews']
            . '-' . ($filterValues['sort'] ? 'desc' : 'asc')
            . '-' . $titleGenresFilterKey
            . '-' . $titleTypesFilterKey;

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

    function getFilterValues(): array
    {
        return [
            'genres' => $this->filterFormData['genres'] ?? null,
            'titleTypes' => $this->filterFormData['titleTypes'] ?? null,
            'minimumAmountReviews' => $this->filterFormData['minimumAmountReviews'] ?? 1,
            'maxAmountReviews' => $this->filterFormData['maxAmountReviews'] ?? Rating::query()->max('number_votes'),
            'sort' => $this->filterFormData['sort'] ?? true,
        ];
    }
}
