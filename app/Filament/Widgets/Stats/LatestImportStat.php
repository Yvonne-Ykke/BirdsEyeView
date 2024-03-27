<?php

namespace App\Filament\Widgets\Stats;

use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Activitylog\Models\Activity;

class LatestImportStat extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Laatste data-import', function () {
                $activity = Activity::query()
                    ->where('description', 'Import Tmdb-data')
                    ->latest()
                    ->first()
                    ->toArray();

                if ($activity) {
                    return Carbon::make($activity['created_at'])->format('d-m-Y');
                }
                return 'Nog niet eerder uitgevoerd';
            })
                ->description('Datum van de laaste data import vanuit de TMDB databron')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('info')

        ];
    }
}
