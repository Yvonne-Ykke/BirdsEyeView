<?php

namespace App\Filament\Widgets\Genres;

use App\Models\Genre;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Set;
use Illuminate\Support\Collection;
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

    protected int | string | array $columnSpan = 2;
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
                    'name' => 'Aantal titels',
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
            $genres = Genre::query();

        return $genres
            ->orderBy('name')
            ->where('name', '!=', '\N')
            ->get();
    }

    private function getChartData(Collection $genres): array
    {
        $genresTitleSum = [];
        foreach ($genres as $genre) {
            $genresTitleSum[] = $this->getGenreTitleSum($genre);
        };

        return $genresTitleSum;
    }

    private function getGenreTitleSum(Genre $genre): int
    {
        return $genre->titles()->count();
    }
}
