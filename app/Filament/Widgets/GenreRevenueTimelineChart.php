<?php

namespace App\Filament\Widgets;

use App\Models\Genre;
use App\Models\Rating;
use App\Models\Title;
use App\Support\Colors;
use Carbon\Carbon;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Collection;
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
                        formatter: function (val, index) {
                            return val + ' mln'
                        }
                    }
                },
                tooltip: {
                    x: {
                        formatter: function (val) {
                            return val + ' mln'
                        }
                    }
                },
            }
    JS
        );
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
                ->maxItems(5)
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
        $query = Title::query()
            ->selectRaw('start_year as x, cast(sum(revenue - budget) / 1000000 as decimal(16)) as y')
            ->join('title_genres', 'titles.id', '=', 'title_genres.title_id')
            ->where('title_genres.genre_id', $genre->id)
            ->where('start_year', '>=', $this->filterFormData['yearFrom']);

        if ($this->filterFormData['yearTill']) {
            $query->where('start_year', '<=', $this->filterFormData['yearTill']);
        }

        return $query->groupBy('start_year')
            ->orderBy('start_year')
            ->get()
            ->toArray();
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

}
