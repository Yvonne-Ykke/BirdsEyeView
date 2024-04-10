<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Charts\General\ProfitRatingChart;
use App\Filament\Widgets\Charts\Genres\GenreProfitPredictionChart;
use App\Filament\Widgets\Custom\CanaryLogoWidget;
use App\Filament\Widgets\Stats\LatestImportStat;
use Closure;
use Filament\Widgets\AccountWidget;
use Illuminate\Support\Facades\Route;


class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = -2;
    protected static ?string $navigationLabel = 'Home';
//    protected static ?string $navigationGroup = 'Dashboards';
    protected static ?string $title = 'Home';

    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ?? static::$title ?? __('filament::pages/dashboard.title');
    }

    public static function getRoutes(): Closure
    {
        return function () {
            Route::get('/', static::class)->name(static::getSlug());
        };
    }

    public function getWidgets(): array
    {
        return [
            AccountWidget::class,
            LatestImportStat::class,
            // ProfitRatingChart::class,        #Chart to show the profit compared to rating. Doesn't load properly.
            CanaryLogoWidget::class,
        ];
    }

//    public function getColumns(): int | string | array
//    {
//        return 3;
//    }

    public function getTitle(): string
    {
        return static::$title ?? __('filament::pages/dashboard.title');
    }
}
