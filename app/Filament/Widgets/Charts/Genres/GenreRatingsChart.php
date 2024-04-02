<?php

namespace App\Filament\Widgets\Charts\Genres;

use App\Filament\Widgets\DefaultFilters\GenreFilter;
use App\Filament\Widgets\DefaultFilters\MaximumAmountReviewsFilter;
use App\Filament\Widgets\DefaultFilters\MinimumAmountReviewsFilter;
use App\Filament\Widgets\DefaultFilters\SortFilter;
use App\Filament\Widgets\DefaultFilters\TitleTypesFilter;
use App\Filament\Widgets\Support\ChartInterface;
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

    function buildQuery(): Builder
    {
        $query = DB::query()
            ->selectRaw("cast(sum(average_rating * number_votes) / sum(number_votes) as decimal(16, 2)) as y, genres.name as x")
            ->from('titles')
            ->join('model_has_ratings', 'titles.id', '=', 'model_has_ratings.model_id')
            ->join('title_genres', 'titles.id', '=', 'title_genres.title_id')
            ->join('genres', 'title_genres.genre_id', '=', 'genres.id')
            ->where('model_has_ratings.number_votes', '>=', (int)$this->filterFormData['minimumAmountReviews'],)
            ->where('model_has_ratings.number_votes', '<=', (int)$this->filterFormData['maxAmountReviews'])
            ->where('genres.name', '!=', '\n')
            ->where('genres.name', '!=', 'Adult')
            ->where('genres.name', '!=', 'undefined')
            ->groupBy('genres.name');

        if (!empty($this->filterFormData['genres'])) {
            $query->whereIn('genres.id', $this->filterFormData['genres']);
        } else {
            $query->limit(10);
        }

        if (!empty($this->filterFormData['titleTypes'])) {
            $query->whereIn('titles.type', $this->filterFormData['titleTypes']);
        }

        if ($this->filterFormData['sort']) {
            $query->orderByDesc('y');
        } else {
            $query->orderBy('y');
        }

        return $query;
    }

    public function getCacheKey(): string
    {
        $titleGenresFilterKey = !empty($this->filterFormData['genres'])
            ? implode('-', $this->filterFormData['genres'])
            : '';

        $titleTypesFilterKey = !empty($this->filterFormData['titleTypes'])
            ? implode('-', $this->filterFormData['titleTypes'])
            : '';

        return 'genreGetAverageRating-'
            . $this->filterFormData['minimumAmountReviews']
            . '-' . $this->filterFormData['maxAmountReviews']
            . '-' . ($this->filterFormData['sort'] ? 'desc' : 'asc')
            . '-' . $titleGenresFilterKey
            . '-' . $titleTypesFilterKey;

    }


    public function getChartData(): array
    {
        $query = $this->buildQuery();
        $cacheKey = $this->getCacheKey();

        return Cache::rememberForever($cacheKey, function () use ($query) {
            return $query
                ->get()
                ->toArray();
        });

    }


}
