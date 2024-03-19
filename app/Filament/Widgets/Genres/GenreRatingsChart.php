<?php

namespace App\Filament\Widgets\Genres;

use App\Models\Genre;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Set;
use Illuminate\Support\Collection;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class GenreRatingsChart extends ApexChartWidget
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

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $genres = $this->getGenres();

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
                    'data' => $this->getChartData($genres),
                ],
            ],
            'xaxis' => [
                'categories' => $genres->pluck('name'),
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
            Select::make('genres')
                ->multiple()
                ->label('Toon enkel')
                ->options(Genre::all()
                    ->where('name', '!=', '\N')
                    ->pluck('name', 'id'))
                ->live()
                ->maxItems(10)
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
                ->afterStateUpdated(function () {
                    $this->updateOptions();
                }),
        ];
    }

    private function getGenres(): \Illuminate\Database\Eloquent\Collection|array
    {
        if (!empty($this->filterFormData['genres'])) {
            $genres = Genre::query()
                ->whereIn('id', $this->filterFormData['genres']);
        } else
            $genres = Genre::query()
                ->limit(10);

        return $genres
            ->orderBy('name')
            ->where('name', '!=', '\N')
            ->get();
    }

    private function getChartData(Collection $genres): array
    {
        $genreWithAverageRating = [];
        foreach ($genres as $genre) {
            $genreWithAverageRating[] = $genre->getAverageRating()['averageRating'];
        }
        return $genreWithAverageRating;
    }
}
