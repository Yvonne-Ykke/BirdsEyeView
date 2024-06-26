<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Charts\Genres\AmountOfTitlesPerGenreChart;
use App\Filament\Widgets\Charts\Genres\GenreProfitPredictionChart;
use App\Filament\Widgets\Charts\Genres\GenreRatingsChart;
use App\Filament\Widgets\Charts\Genres\GenreRevenueTimelineChart;
use Closure;
use Illuminate\Support\Facades\Route;


class GenreDashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string $routePath = 'genre-dashboard';
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationGroup = 'Dashboards';

    protected static ?string $navigationLabel = 'Genres';

    protected static ?string $title = 'Genre dashboard';


    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ?? static::$title ?? __('filament::pages/dashboard.title');
    }

    public static function getRoutes(): Closure
    {
        return function () {
            Route::get('/genre-dashboard', static::class)->name(static::getSlug());
        };
    }

    public function getWidgets(): array
    {
        return [
            GenreRatingsChart::class,
            GenreRevenueTimelineChart::class,
            AmountOfTitlesPerGenreChart::class,
            GenreProfitPredictionChart::class,

        ];
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }

    public function getTitle(): string
    {
        return static::$title ?? __('filament::pages/dashboard.title');
    }
}
