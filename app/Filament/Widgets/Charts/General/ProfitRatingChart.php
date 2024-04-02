<?php

namespace App\Filament\Widgets\Charts\General;

use App\Filament\Widgets\DefaultFilters\GenreFilter;
use App\Filament\Widgets\DefaultFilters\TitleTypesFilter;
use App\Models\Genre;
use App\Models\Rating;
use App\Support\Enums\Colors;
use Doctrine\DBAL\Query;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Set;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ProfitRatingChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static string $chartId = 'ProfitRating';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Winstpercentage per beoordeling';

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
                'type' => 'scatter',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Winstpercentage per beoordeling',
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
            'colors' => Colors::INDIGO,
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

    private function getChartData(): array
    {
        $query = Rating::query()
        ->selectRaw('CAST(SUM(average_rating * number_votes) / SUM(number_votes) AS DECIMAL(16, 2)) AS y')
        ->selectRaw('CASE 
                WHEN revenue >= budget THEN ((CAST(revenue AS DECIMAL(16,2)) - CAST(budget AS DECIMAL(16,2))) / CAST(budget AS DECIMAL(16,2))) * 100
                ELSE -((CAST(budget AS DECIMAL(16,2)) - CAST(revenue AS DECIMAL(16,2))) / CAST(revenue AS DECIMAL(16,2))) * 100
                END AS x')
        ->from('titles')
        ->join('model_has_ratings', 'titles.id', '=', 'model_has_ratings.model_id')
        ->whereNotNull('revenue')
        ->whereNotNull('budget')
        ->where('revenue', '!=', 0)
        ->where('budget', '!=', 0)     
        ->where('model_has_ratings.number_votes', '>=', 50)
        ->orderBy('y')
        ->groupBy('x')
        ->limit(10);
    
        $titleGenreFilterKey = '';
        $titleTypesFilterKey = '';

        if (!empty($this->filterFormData['genres'])) {
            $query->whereIn('genres.id', $this->filterFormData['genres']);
            $titleGenreFilterKey = implode('-', $this->filterFormData['genres']);
        }

        if (!empty($this->filterFormData['titleTypes'])) {
            $query->join('titles', 'title_genres.title_id', 'titles.id')
                ->whereIn('titles.type', $this->filterFormData['titleTypes']);
            $titleTypesFilterKey = implode('-', $this->filterFormData['titleTypes']);
        }

        $values = $query
                ->get()
                ->toArray();

        $cacheKey = 'ProfitRatingChart'
            . '-' . $titleGenreFilterKey
            . '-' . $titleTypesFilterKey;

            return $values;

    }
}
