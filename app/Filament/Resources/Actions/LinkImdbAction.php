<?php

namespace App\Filament\Resources\Actions;

use App\Models\People;
use Filament\Forms\Components\Actions\Action;

class LinkImdbAction
{
    static function action(): Action
    {
        return Action::make('linkImdb')
            ->color('primary')
            ->label('Ga naar imdb pagina')
            ->icon('heroicon-m-film')
            ->hidden(function (?People $record) {
                return $record?->imdb_externid === null;
            })
            ->action(function (?People $record) {
                return redirect('https://www.imdb.com/name/' . $record->imdb_externid);
            });
    }
}
