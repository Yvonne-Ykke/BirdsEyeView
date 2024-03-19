<?php

namespace App\Filament\Widgets;

use App\Models\Genre;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;

class SumGenreMoviesChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected int|string|array $columnSpan = 2;

    private ?Collection $genres;


    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        $this->genres = Genre::all()->sortBy('name');

        return [
            'datasets' => [
                [
                    'label' => 'titles',
                    'data' => $this->getGenreTitlesSum(),
                    'fill' => 'start',
                ],
            ],
            'labels' => $this->genres->pluck('name'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return $this->genres->pluck('name', 'id')->toArray();
    }

    private function getGenreTitlesSum(): array
    {
        $genresTitleSum = [];
        foreach ($this->genres as $genre) {
            $genresTitleSum[] = $this->getGenreTitleSum($genre);
        };

        return $genresTitleSum;
    }

    private function getGenreTitleSum(Genre $genre): int
    {
        return $genre->titles()->count();
    }
}
