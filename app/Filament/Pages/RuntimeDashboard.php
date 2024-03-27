<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Charts\Runtime\RuntimeChart;
use App\Filament\Widgets\Charts\Runtime\RuntimeRatingChart;
use Illuminate\Support\Facades\Route;


class RuntimeDashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static string $routePath = 'runtime-dashboard';
    protected static ?int $navigationSort = 0;

    protected static ?string $navigationLabel = 'Runtime';

    protected static ?string $title = 'Runtime dashboard';


    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ?? static::$title ?? __('filament::pages/dashboard.title');
    }

    public static function getRoutes(): \Closure
    {
        return function () {
            Route::get('/runtime-dashboard', static::class)->name(static::getSlug());
        };
    }

    public function getWidgets(): array
    {
        return [
            #RuntimeChart::class,
            RuntimeRatingChart::class,
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
