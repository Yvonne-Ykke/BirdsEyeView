<?php

namespace App\Filament\Resources\Actions;

use App\Models\People;
use Filament\Forms\Components\Actions\Action;

class LinkTmdbAction
{
    static function action(): Action
    {
        return Action::make('linkTmdb')
            ->color('info')
            ->label('Ga naar tmdb pagina')
            ->icon('heroicon-m-video-camera')
            ->hidden(function (?People $record) {
                return $record?->tmdb_externid === null;
            })
            ->action(function (?People $record) {
                return redirect('https://www.themoviedb.org/person/' . $record->tmdb_externid);
            });
    }
}
