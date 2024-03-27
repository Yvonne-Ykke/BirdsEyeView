<?php

namespace App\Filament\Widgets\Charts\Runtime;

use App\Models\Genre;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Illuminate\Support\Collection;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class RuntimeChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static string $chartId = 'runtimeChart';

    protected int | string | array $columnSpan = 2;

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Gemiddelde runtime per genre';

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
                    'name' => 'Gemiddelde runtime',
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
                'max' => 150,
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
                    ->where('name', '!=', '')
                    ->where('name', '!=', 'Adult')
                    ->where('name', '!=', 'Short')
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
        ];
    }

    private function getGenres(): \Illuminate\Database\Eloquent\Collection|array
    {
        if (!empty($this->filterFormData['genres'])) {
            $genres = Genre::query()
                ->whereIn('id', $this->filterFormData['genres']);
        } else
            $genres = Genre::query()
                ;

        return $genres
            ->orderBy('name')
            ->where('name', '!=', '\N')
            ->where('name', '!=', 'Adult')
            ->where('name', '!=', 'Short')
            ->has('titles')
            ->get();
    }

    private function getChartData(Collection $runtime, ): array
    {
        $genreWithAverageRuntime = [];
        foreach ($runtime as $runtime) {
            if ($runtime != null) {
            $genreWithAverageRuntime[] = $runtime->getAverageRuntime(
            ['movie']
            )['averageRuntime'];
        }}
        return $genreWithAverageRuntime;
    }
}
