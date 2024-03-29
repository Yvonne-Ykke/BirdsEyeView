<?php

namespace App\Filament\Widgets\Charts\Shows;

use App\Filament\Widgets\DefaultFilters\MaximumAmountReviewsFilter;
use App\Filament\Widgets\DefaultFilters\MinimumAmountReviewsFilter;
use App\Models\Title;
use App\Support\Enums\Colors;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ShowsRatingEpisodesChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static string $chartId = 'showsRatingEpisodesChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Aantal aflevering per gemiddelde recensie';

    protected static ?string $pollingInterval = null;

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $data = $this->getChartData();
        return [
            'chart' => [
                'type' => 'scatter',
                'height' => 300,
            ],
            'series' => $data,
            'xaxis' => [
                'tickAmount' => 7,
                'categories' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'tickAmount' => 7,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'legend' => [
                'labels' => [
                    'fontFamily' => 'inherit',
                ],
            ],
            'colors' => Colors::getStatic(count($data)),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            MinimumAmountReviewsFilter::get()
                ->default(10000),
            MaximumAmountReviewsFilter::get(),
        ];

    }

    public function getChartData(): array
    {
        $query = $this->buildQuery();

        return $this->resetResultArrayKeys(function () use ($query) {
            return Cache::rememberForever(
                key: $this->getCacheKey(),
                callback: function () use ($query) {
                    return $query->get()->toArray();
                });
        });
    }


    /**/
    public function buildQuery(): Builder
    {
        return Title::query()
            ->select('rounded_rating', 'episodes')
            ->fromSub(function ($query) {
                $query->select('episodes', 'rounded_rating', DB::raw('ROW_NUMBER() OVER(PARTITION BY episodes, rounded_rating ORDER BY episodes DESC) AS row_num'))
                    ->fromSub(function ($innerQuery) {
                        $innerQuery->selectRaw('COUNT(te.id) as episodes')
                            ->selectRaw('CAST(ROUND(average_rating) AS INTEGER) as rounded_rating')
                            ->from('titles')
                            ->join('title_episodes AS te', 'titles.id', '=', 'te.parent_title_id')
                            ->join('model_has_ratings', 'titles.id', '=', 'model_has_ratings.model_id')
                            ->where('type', 'tvSeries')
                            ->where('number_votes', '>', $this->filterFormData['minimumAmountReviews'])
                            ->where('number_votes', '<', $this->filterFormData['maxAmountReviews'])
                            ->groupBy('primary_title', 'rounded_rating');
                    }, 'subquery');
            }, 'subquery2')
            ->where('row_num', 1)
            ->limit(1000);
    }

    private function resetResultArrayKeys(\Closure $closure): array
    {
        $values = $closure();
        $numberDots = count($values);
        $result = $this->getSeriesDataStructure();

        for ($i = 0; $i < $numberDots; $i++) {
            $value = array_values($values[$i]);
            $result = $this->splitValuesIntoCategories($result, $value);
        }

        return array_values($result);
    }

    private function getSeriesDataStructure(): array
    {
        return [
            '0-50' => [
                'name' => '0-50',
                'data' => [

                ],
            ],
            '50-100' => [
                'name' => '50-100',
                'data' => [

                ],
            ],
            '100-350' => [
                'name' => '100-350',
                'data' => [

                ],
            ],
            '350-500' => [
                'name' => '350-500',
                'data' => [

                ],
            ],
            '500-1000' => [
                'name' => '500-1000',
                'data' => [

                ],
            ],
            '>1000' => [
                'name' => '>1000',
                'data' => [

                ],
            ]
        ];
    }

    private function splitValuesIntoCategories(array $result, array $values): array
    {
        if ($values[1] >= 1000) {
            $result['>1000']['data'][] = $values;
        } else if ($values[1] >= 500) {
            $result['500-1000']['data'][] = $values;
        } else if ($values[1] >= 350) {
            $result['350-500']['data'][] = $values;
        } else if ($values[1] >= 100) {
            $result['100-350']['data'][] = $values;
        } else if ($values[1] >= 50) {
            $result['50-100']['data'][] = $values;
        } else {
            $result['0-50']['data'][] = $values;
        }

        return $result;
    }

    public function getCacheKey(): string
    {
        return 'showsRatingEpisodesChart'
            . '-' . $this->filterFormData['minimumAmountReviews']
            . '-' . $this->filterFormData['maxAmountReviews'];
    }
}
