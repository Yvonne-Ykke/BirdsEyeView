<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Charts\Shows\ShowsRatingsGenreChart;
use App\Filament\Widgets\Tables\Actors\BestActorsTableWidget;
use Closure;
use Illuminate\Support\Facades\Route;


class ActorsDashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static string $routePath = 'actors-dashboard';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Acteurs';
    protected static ?string $navigationGroup = 'Dashboards';
    protected static ?string $title = 'Acteurs dashboard';


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
            BestActorsTableWidget::class,
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
