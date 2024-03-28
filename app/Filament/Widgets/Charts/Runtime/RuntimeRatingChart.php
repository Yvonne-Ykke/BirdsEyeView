<?php

namespace App\Filament\Widgets\Charts\Runtime;

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
use Illuminate\Database\Eloquent\Collection;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class RuntimeRatingChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static string $chartId = 'runtimeRatingChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Beoordeling van films met verschillende tijdsduren';

    protected int|string|array $columnSpan = 2;

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

        $genreWithRuntimeRating = $this->getChartOptions();

        return [
            'chart' => [
                'type' => 'line',
                'height' => 300,
            ],
            'series' => $genreWithRuntimeRating,
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
            'colors' => Colors::getRandom(count($genreWithRuntimeRating)),
            'stroke' => [
                'curve' => 'straight',
                'width' => 3
            ],
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('genres')
                ->label('Toon enkel')
                ->options(Genre::all()
                    ->where('name', '!=', '\N')
                    ->where('name', '!=', '')
                    ->where('name', '!=', 'Adult')
                    ->where('name', '!=', 'Short')
                    ->pluck('name', 'id'))
                ->live()
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

        foreach ($genres as $genre) {
            $options[] = [
                'data' => $genre->getRuntimeRating(),
                'name' => $genre->name,

            ];
        }
        return $options;
    }

    private function getGenres(): Collection|array
    {
        $query = Genre::query();

        if ($this->filterFormData['genres']) {
            $query->where('id', $this->filterFormData['genres']);
        } else {
            $query->limit(1);
        }

        return $query
            ->orderBy('name')
            ->where('name', '!=', '\N')
            ->where('name', '!=', 'Adult')
            ->get();
    }


}
