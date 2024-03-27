<?php

namespace App\Filament\Widgets\Charts\Genres;

use App\Models\Genre;
use App\Models\Rating;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
                    'data' => $this->getChartData(),
                ],
            ],
            'xaxis' => [
//                'categories' => $genres->pluck('name'),
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
            Toggle::make('sort')
                ->label('Sorteer hoog -> laag')
                ->required()
                ->live()
                ->default(true)
                ->afterStateUpdated(function () {
                    $this->updateOptions();
                }),
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
                ->searchable(),
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
                })
                ->helperText('Door grote hoeveelheid data kunnen deze filters traag zijn (10-15s)'),
        ];
    }

    protected function getLoadingIndicator(): null|string|View
    {
        return view('components.loading-icons.ball-clip-rotate-multiple');
    }


    private function getChartData(): array
    {

        $query = DB::query()
            ->selectRaw("cast(sum(average_rating * number_votes) / sum(number_votes) as decimal(16, 2)) as y, genres.name as x")
            ->from('titles')
            ->join('model_has_ratings', 'titles.id', '=', 'model_has_ratings.model_id')
            ->join('title_genres', 'titles.id', '=', 'title_genres.title_id')
            ->join('genres', 'title_genres.genre_id', '=', 'genres.id')
            ->where('model_has_ratings.number_votes', '>=', (int)$this->filterFormData['minimumAmountReviews'],)
            ->where('model_has_ratings.number_votes', '<=', (int)$this->filterFormData['maxAmountReviews'])
            ->where('genres.name', '!=', '\n')
            ->where('genres.name', '!=', 'Adult')
            ->where('genres.name', '!=', 'undefined')
            ->groupBy('genres.name');

        if (!empty($this->filterFormData['genres'])) {
            $query->whereIn('genres.id', $this->filterFormData['genres']);
        } else {
            $query->limit(10);
        }

        if ($this->filterFormData['sort']) {
            $query->orderByDesc('y');
        } else {
            $query->orderBy('y');
        }

        $cacheKey = $this->getCacheKey();
        return Cache::rememberForever($cacheKey, function () use ($query) {
            return $query
                ->get()
                ->toArray();
        });

    }

    private function getCacheKey(): string
    {
        $titleGenresFilterKey = !empty($this->filterFormData['genres'])
            ? implode('-', $this->filterFormData['genres'])
            : '';

        return 'genreGetAverageRating-'
            . $this->filterFormData['minimumAmountReviews']
            . '-' . $this->filterFormData['maxAmountReviews']
            . '-' . ($this->filterFormData['sort'] ? 'desc' : 'asc')
            . '-' . $titleGenresFilterKey;

    }
}
