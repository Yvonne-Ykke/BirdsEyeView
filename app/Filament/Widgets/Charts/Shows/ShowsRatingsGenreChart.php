<?php

namespace App\Filament\Widgets\Charts\Shows;

use App\Filament\Widgets\Charts\DefaultChartFilters\GenreFilter;
use App\Filament\Widgets\Charts\DefaultChartFilters\MaximumAmountReviewsFilter;
use App\Filament\Widgets\Charts\DefaultChartFilters\MinimumAmountReviewsFilter;
use App\Filament\Widgets\Charts\DefaultChartFilters\SortFilter;
use App\Filament\Widgets\Charts\Genres\GenreRatingsChart;

class ShowsRatingsGenreChart extends GenreRatingsChart
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static string $chartId = 'genreRatingsChart';

    protected int|string|array $columnSpan = 1;


    public function __construct()
    {
        $this->filterFormData['titleTypes'] = ['tvSeries'];
    }

    protected function getFormSchema(): array
    {
        return [
            SortFilter::get(),
            GenreFilter::get()->maxItems(10),
            MinimumAmountReviewsFilter::get(),
            MaximumAmountReviewsFilter::get()
                ->helperText('Door grote hoeveelheid te verwerken data kunnen deze filters traag zijn (10-15s)'),
        ];
    }

}
