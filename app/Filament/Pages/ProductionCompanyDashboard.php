<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Charts\Genres\AmountOfTitlesPerGenreChart;
use App\Filament\Widgets\Charts\Genres\GenreRatingsChart;
use App\Filament\Widgets\Charts\Genres\GenreRevenueTimelineChart;
use Closure;
use Illuminate\Support\Facades\Route;


class ProductionCompanyDashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static string $routePath = 'production-company-dashboard';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationGroup = 'Dashboards';

    protected static ?string $navigationLabel = 'Productie bedrijven';

    protected static ?string $title = 'Productie bedrijven dashboard';


    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ?? static::$title ?? __('filament::pages/dashboard.title');
    }

    public static function getRoutes(): Closure
    {
        return function () {
            Route::get('/production-company-dashboard', static::class)->name(static::getSlug());
        };
    }

    public function getWidgets(): array
    {
        return [

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
