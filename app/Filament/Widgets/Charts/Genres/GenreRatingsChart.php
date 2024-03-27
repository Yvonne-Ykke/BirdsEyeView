<?php

namespace App\Filament\Widgets\Charts\Genres;

use App\Models\Genre;
use App\Models\Rating;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Illuminate\Contracts\View\View;
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

    protected static ?string $pollingInterval = null;

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
                    ->where('name', '!=', 'Adult')
                    ->pluck('name', 'id'))
                ->live()
                ->maxItems(10)
                ->hintAction(
                    Action::make('clearField')
                        ->label('Reset')
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
            TextInput::make('minimumAmountReviews')
                ->label('Minimaal aantal reviews')
                ->live()
                ->default(0)
                ->required()
                ->numeric()
                ->minValue(0)
                ->hintAction(
                    Action::make('clearField')
                        ->label('Reset')
                        ->icon('heroicon-m-trash')
                        ->action(function (Set $set) {
                            $set('minimumAmountReviews', 0);
                        })
                )
                ->afterStateUpdated(function () {
                    $this->updateOptions();
                }),
            TextInput::make('maxAmountReviews')
                ->label('Maximaal aantal reviews')
                ->live()
                ->default(Rating::query()->max('number_votes'))
                ->required()
                ->minValue(0)
                ->numeric()
                ->hintAction(
                    Action::make('clearField')
                        ->label('Reset')
                        ->icon('heroicon-m-trash')
                        ->action(function (Set $set) {
                            $set('maxAmountReviews', Rating::query()->max('number_votes'));
                        })
                )
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
            ->where('name', '!=', 'Adult')
            ->get();
    }
    protected function getLoadingIndicator(): null|string|View
    {
        return view('components.loading-icons.ball-clip-rotate-multiple');
    }


    private function getChartData(Collection $genres, ): array
    {
        $genreWithAverageRating = [];
        foreach ($genres as $genre) {
            $genreWithAverageRating[] = $genre->getAverageRating(
                (int)$this->filterFormData['minimumAmountReviews'],
                (int)$this->filterFormData['maxAmountReviews'],
            )['averageRating'];
        }
        return $genreWithAverageRating;
    }
}
