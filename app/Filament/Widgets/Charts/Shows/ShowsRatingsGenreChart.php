<?php

namespace App\Filament\Widgets\Charts\Shows;

use App\Filament\Widgets\Charts\Genres\GenreRatingsChart;
use App\Filament\Widgets\DefaultFilters\GenreFilter;
use App\Filament\Widgets\DefaultFilters\MaximumAmountReviewsFilter;
use App\Filament\Widgets\DefaultFilters\MinimumAmountReviewsFilter;
use App\Filament\Widgets\DefaultFilters\SortFilter;

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
            GenreFilter::get(),
            MinimumAmountReviewsFilter::get(),
            MaximumAmountReviewsFilter::get()
                ->helperText('Door grote hoeveelheid te verwerken data kunnen deze filters traag zijn (10-15s)'),
        ];
    }

}
