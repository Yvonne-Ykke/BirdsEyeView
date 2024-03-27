<?php

namespace App\Filament\Widgets\Charts\Genres;

use App\Models\Genre;
use Doctrine\DBAL\Query;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Set;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class AmountOfTitlesPerGenreChart extends ApexChartWidget
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
            Select::make('genres')
                ->multiple()
                ->label('Toon enkel')
                ->options(Genre::all()
                    ->where('name', '!=', '\N')
                    ->where('name', '!=', 'Adult')
                    ->pluck('name', 'id'))
                ->live()
                ->hintAction(
                    Action::make('clearField')
                        ->label('Reset invoerveld')
                        ->icon('heroicon-m-trash')
                        ->action(function (Set $set) {
                            $set('genres', []);
                        })
                )
                ->native(false)
                ->searchable()
                ->maxItems(15)
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
        $query = Genre::query()
            ->selectRaw('count(title_id) as y, genres.name as x')
            ->join('title_genres', 'genres.id', 'title_genres.genre_id')
            ->where('name', '!=', '\N')
            ->where('name', '!=', 'Adult')
            ->where('name', '!=', 'undefined')
            ->orderByDesc('y')
            ->groupBy(['genre_id', 'name']);


        $titleGenreFilterKey = '';
        if (!empty($this->filterFormData['genres'])) {
            $query->whereIn('genres.id', $this->filterFormData['genres']);

            $titleGenreFilterKey = !empty($this->filterFormData['genres'])
                ? implode('-', $this->filterFormData['genres'])
                : '';
        }

        $cacheKey = 'AmountOfTitlesPerGenreChart-' . $titleGenreFilterKey;
        return Cache::rememberForever($cacheKey, function () use ($query) {
            return $query->get()
                ->toArray();
        });
    }
}
