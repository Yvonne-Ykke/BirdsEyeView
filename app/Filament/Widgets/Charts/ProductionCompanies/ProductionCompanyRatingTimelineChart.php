<?php

namespace App\Filament\Widgets\Charts\ProductionCompanies;

use App\Filament\Widgets\Charts\DefaultChartFilters\MinimalAmountOfProductionsFilter;
use App\Filament\Widgets\Charts\DefaultChartFilters\ProductionCompaniesFilter;
use App\Filament\Widgets\Charts\DefaultChartFilters\YearFromFilter;
use App\Filament\Widgets\Charts\DefaultChartFilters\YearTillFilter;
use App\Filament\Widgets\Support\ChartInterface;
use App\Models\ProductionCompany;
use App\Models\Title;
use App\Support\Enums\Colors;
use Filament\Forms\Get;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class ProductionCompanyRatingTimelineChart extends ProductionCompanyRevenueTimelineChart implements ChartInterface
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static string $chartId = 'ProductionCompanyRatingTimelineChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Bedrijf gemiddelde recensies tijdlijn';

    protected static ?string $pollingInterval = null;

    protected function getOptions(): array
    {
        if (!$this->readyToLoad) {
            return [];
        }

        $options = $this->getChartData();

        return [
            'chart' => [
                'type' => 'line',
                'height' => 300,
            ],
            'series' => $options,
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
            'colors' => Colors::getRandom(max(count($options), 1)),
            'stroke' => [
                'curve' => 'straight',
                'width' => 3
            ],
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            MinimalAmountOfProductionsFilter::get(),
            ProductionCompaniesFilter::get()
                ->options((function (Get $get) {
                    $minimalAmountOfReviews = $get('minimalAmountOfProductions');
                    return Cache::rememberForever(
                        key: 'ProductionCompaniesRatingFilter-' . max($get('minimalAmountOfProductions'), 1),
                        callback: function () use ($minimalAmountOfReviews) {
                            return ProductionCompany::query()
                                ->join('model_has_production_company as mhpc', 'production_companies.id', '=', 'mhpc.production_company_id')
                                ->join('titles', 'mhpc.model_id', '=', 'titles.id')
                                ->join('model_has_ratings as mhr', 'titles.id', '=', 'mhr.model_id')
                                ->where('mhr.number_votes', '>', 0)
                                ->groupBy('production_companies.id')
                                ->groupBy(['production_companies.name', 'production_companies.id'])
                                ->havingRaw('COUNT(DISTINCT mhpc.id) > ' . max($minimalAmountOfReviews, 1))
                                ->pluck('production_companies.name', 'production_companies.id');
                        });
                }))
                ->helperText('Staat er een bedrijf niet tussen? Het kan zijn dat we daar geen recensie gegevens over hebben')
                ->maxItems(3),
            YearFromFilter::get(),
            YearTillFilter::get(),
        ];
    }


    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
            {}
        JS
        );
    }

    public function getCacheKey(array $filterValues): string
    {
        return 'getCompanyRatingTimeline-'
            . '-' . $filterValues['yearTill']
            . '-' . $filterValues['yearFrom']
            . '-' . $filterValues['productionCompanyId'];
    }


    public function buildQuery(array $filterValues): Builder
    {
        $query = Title::query()
            ->selectRaw('start_year as x, cast(sum(average_rating * number_votes) / sum(number_votes) as decimal(16, 2)) as y')
            ->join('model_has_production_company as mhpc', function ($join) use ($filterValues) {
                $join->on('titles.id', '=', 'mhpc.model_id')
                    ->where('mhpc.production_company_id', '=', $filterValues['productionCompanyId']);
            })
            ->join('model_has_ratings as mhr', 'titles.id', '=', 'mhr.model_id')
            ->where('mhr.number_votes', '>', 0)
            ->where('start_year', '>=', $filterValues['yearFrom'])
            ->groupBy('x');

        if ($filterValues['yearTill']) {
            $query->where('end_year', '<=', $filterValues['yearTill']);
        }

        return $query;
    }

}
