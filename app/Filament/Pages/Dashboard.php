<?php

namespace App\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Pages\Dashboard as BasePage;
use Illuminate\Support\Facades\Route;
use Closure;


class Dashboard extends BasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = -2;

    protected static string $view = 'filament::pages.dashboard';

//    public static function getNavigationLabel(): string
//    {
//        return static::$navigationLabel ?? static::$title ?? __('filament::pages/dashboard.title');
//    }

    public static function getRoutes(): Closure
    {
        return function () {
            Route::get('/', static::class)->name(static::getSlug());
        };
    }

    public function getWidgets(): array
    {
        return Filament::getWidgets();
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
