<?php

namespace App\Filament\Widgets\Charts\ProductionCompanies;

use App\Filament\Widgets\Charts\DefaultChartFilters\GenreFilter;
use App\Filament\Widgets\Charts\DefaultChartFilters\ProductionCompaniesFilter;
use App\Filament\Widgets\Charts\DefaultChartFilters\TitleTypesFilter;
use App\Filament\Widgets\Charts\DefaultChartFilters\YearFromFilter;
use App\Filament\Widgets\Charts\DefaultChartFilters\YearTillFilter;
use App\Filament\Widgets\Support\ChartInterface;
use App\Models\Genre;
use App\Models\ProductionCompany;
use App\Models\Title;
use App\Support\Enums\Colors;
use Carbon\Carbon;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Support\RawJs;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ProductionCompanyRevenueTimelineChart extends ApexChartWidget implements ChartInterface
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static string $chartId = 'genreRevenueTimelineChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Bedrijf winst tijdlijn';

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
            ],
            'colors' => Colors::getRandom(max(count($options), 1)),
            'stroke' => [
                'curve' => 'straight',
                'width' => 3
            ],
        ];
    }

    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
            {
                yaxis: {
                    labels: {
                        formatter: function(val, index) {
                            return val + ' mln'
                        }
                    }
                }
            }
    JS
        );
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('minimalAmountOfProductions')
                ->label('Minimaal aantal gemaakte producties')
                ->live(debounce: 500)
                ->default(20)
                ->required()
                ->numeric()
                ->minValue(1)
                ->hintAction(
                    Action::make('clearField')
                        ->label('Reset')
                        ->icon('heroicon-m-trash')
                        ->action(function (Set $set) {
                            $set('minimalAmountOfProductions', 1950);
                        })
                ),
            ProductionCompaniesFilter::get()
                ->maxItems(3),
            YearFromFilter::get(),
            YearTillFilter::get(),
        ];
    }

    protected function getLoadingIndicator(): null|string|View
    {
        return view('components.loading-icons.ball-clip-rotate-multiple');
    }

    public function getChartData(): array
    {
        $productionCompanies = $this->getProductionCompanies();
        $options = [];
        $i = 0;

        foreach ($productionCompanies as $company) {
            $this->filterFormData['productionCompanyId'] = $company->id;
            $options[$i]['data'] = $this->getCompanyRevenueTimeline();
            $options[$i]['name'] = $company->name;
            $i++;
        }

        return $options;
    }

    private function getCompanyRevenueTimeline(): array
    {
        $filters = $this->getFilterValues();
        $query = $this->buildQuery($filters);
        $cacheKey = $this->getCacheKey($filters);

        return Cache::rememberForever($cacheKey, function () use ($query) {
            return $query
                ->orderBy('start_year')
                ->get()
                ->toArray();
        });
    }

    public function getCacheKey(array $filterValues): string
    {
        return 'getCompanyRevenueTimeline-'
            . '-' . $filterValues['yearTill']
            . '-' . $filterValues['yearFrom']
            . '-' . $filterValues['productionCompanyId'];
    }


    public function buildQuery(array $filterValues): Builder
    {
        $query = Title::query()
            ->selectRaw('start_year as x, cast(sum(revenue - budget) / 1000000 as decimal(16)) as y')
            ->join('model_has_production_company as mhpc', function ($join) use ($filterValues) {
                $join->on('titles.id', '=', 'mhpc.model_id')
                    ->where('mhpc.production_company_id', '=', $filterValues['productionCompanyId']);
            })
            ->where('revenue', '>', 0)
            ->where('budget', '>', 0)
            ->where('start_year', '>=', $filterValues['yearFrom'])
            ->groupBy('x');

        if ($filterValues['yearTill']) {
            $query->where('end_year', '<=', $filterValues['yearTill']);
        }

        return $query;
    }

    function getFilterValues(): array
    {
        return [
            'minimalAmountOfProductions' => $this->filterFormData['minimalAmountOfProductions'] ?? 20,
            'productionCompanies' => $this->filterFormData['productionCompanies'] ?? null,
            'productionCompanyId' => $this->filterFormData['productionCompanyId'] ?? ProductionCompany::first()?->id,
            'yearTill' => $this->filterFormData['yearTill'] ?? null,
            'yearFrom' => $this->filterFormData['yearFrom'] ?? 1950,
        ];
    }

    private function getProductionCompanies(): Collection|array
    {
        return ProductionCompany::query()
            ->whereIn('id', ($this->filterFormData['productionCompanies'] ?? []))
            ->get();
    }
}
