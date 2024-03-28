<?php

namespace App\Filament\Resources\Actions;

use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class LinkTmdbAction
{
    static function action(string $endpoint): Action
    {
        return Action::make('linkTmdb')
            ->color('info')
            ->label('Ga naar tmdb pagina')
            ->icon('heroicon-m-video-camera')
            ->hidden(function (?Model $record) {
                return $record?->tmdb_externid === null;
            })
            ->action(function (?Model $record) use($endpoint) {
                return redirect('https://www.themoviedb.org/' . $endpoint . $record->tmdb_externid);
            });
    }
}
