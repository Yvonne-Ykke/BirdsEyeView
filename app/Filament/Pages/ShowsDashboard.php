<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Charts\Genres\GenreRatingsChart;
use App\Filament\Widgets\Charts\Shows\ShowsRatingEpisodesChart;
use App\Filament\Widgets\Charts\Shows\ShowsRatingsGenreChart;
use Closure;
use Illuminate\Support\Facades\Route;


class ShowsDashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-video-camera';
    protected static string $routePath = 'shows-dashboard';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Series';
    protected static ?string $navigationGroup = 'Dashboards';
    protected static ?string $title = 'Series dashboard';


    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ?? static::$title ?? __('filament::pages/dashboard.title');
    }

    public static function getRoutes(): Closure
    {
        return function () {
            Route::get('/shows-dashboard', static::class)->name(static::getSlug());
        };
    }

    public function getWidgets(): array
    {
        return [
            ShowsRatingsGenreChart::class,
            ShowsRatingEpisodesChart::class,
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
