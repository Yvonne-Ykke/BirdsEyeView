<?php

namespace App\Filament\Widgets\Charts\Genres;

use App\Filament\Widgets\DefaultFilters\GenreFilter;
use App\Filament\Widgets\DefaultFilters\TitleTypesFilter;
use App\Models\Genre;
use App\Models\Title;
use App\Support\Enums\Colors;
use Carbon\Carbon;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Filament\Support\RawJs;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class GenreRevenueTimelineChart extends ApexChartWidget
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
    protected static ?string $heading = 'Genre winst tijdlijn';

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

        $options = $this->getChartOptions();

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
            'colors' => Colors::getRandom(count($options)),
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
            GenreFilter::get()
                ->maxItems(5)
                ->afterStateUpdated(function () {
                    $this->updateOptions();
                }),
            TitleTypesFilter::get(),
            TextInput::make('yearFrom')
                ->label('Vanaf')
                ->live()
                ->default(1950)
                ->required()
                ->numeric()
                ->minValue(0)
                ->hintAction(
                    Action::make('clearField')
                        ->label('Reset')
                        ->icon('heroicon-m-trash')
                        ->action(function (Set $set) {
                            $set('yearFrom', 1950);
                        })
                )
                ->afterStateUpdated(function () {
                    $this->updateOptions();
                }),
            TextInput::make('yearTill')
                ->label('Tot')
                ->live()
                ->minValue(0)
                ->placeholder(Carbon::now()->year)
                ->gt('yearFrom')
                ->numeric()
                ->hintAction(
                    Action::make('clearField')
                        ->label('Reset')
                        ->icon('heroicon-m-trash')
                        ->action(function (Set $set) {
                            $set('yearTill', null);
                        })
                )
                ->afterStateUpdated(function () {
                    $this->updateOptions();
                }),
        ];
    }

    protected function getLoadingIndicator(): null|string|View
    {
        return view('components.loading-icons.ball-clip-rotate-multiple');
    }

    private function getChartOptions(): array
    {
        $genres = $this->getGenres();
        $options = [];
        $i = 0;

        foreach ($genres as $genre) {

            $options[$i]['data'] = $this->getGenreRevenueTimeline($genre);
            $options[$i]['name'] = $genre->name;
            $i++;
        }
        return $options;
    }

    private function getGenreRevenueTimeline(Genre $genre): array
    {
        $query = $this->buildQuery($genre);

        $cacheKey = $this->getCacheKey($genre);
        return Cache::rememberForever($cacheKey, function () use ($query) {
            return $query->groupBy('start_year')
                ->orderBy('start_year')
                ->get()
                ->toArray();
        });
    }

    private function getCacheKey(Genre $genre): string
    {
        $yearTillKey = $this->filterFormData['yearTill'] ?? '' ;

        $titleTypesFilterKey = !empty($this->filterFormData['titleTypes'])
            ? implode('-', $this->filterFormData['titleTypes'])
            : '';

        return 'getGenreRevenueTimeline-'
            . str_replace(' ', '-', $genre->name)
            . '-' . $yearTillKey
            . '-' . $this->filterFormData['yearFrom']
            . '-' . $titleTypesFilterKey;
    }

    private function getGenres(): Collection|array
    {
        $query = Genre::query();

        if ($this->filterFormData['genres']) {
            $query->whereIn('id', $this->filterFormData['genres']);
        } else {
            $query->limit(3);
        }

        return $query
            ->orderBy('name')
            ->where('name', '!=', '\N')
            ->where('name', '!=', 'Adult')
            ->get();
    }

    private function buildQuery(Genre $genre): Builder
    {
        $query = Title::query()
            ->selectRaw('start_year as x, cast(sum(revenue - budget) / 1000000 as decimal(16)) as y')
            ->join('title_genres', 'titles.id', '=', 'title_genres.title_id')
            ->where('title_genres.genre_id', $genre->id)
            ->where('revenue', '>', 0)
            ->where('budget', '>', 0)
            ->where('start_year', '>=', $this->filterFormData['yearFrom']);

        if ($this->filterFormData['yearTill']) {
            $query->where('end_year', '<=', $this->filterFormData['yearTill']);
        }

        if (!empty($this->filterFormData['titleTypes'])) {
            $query->whereIn('titles.type', $this->filterFormData['titleTypes']);
        }

        return $query;
    }

}
