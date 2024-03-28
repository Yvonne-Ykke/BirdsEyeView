<?php

namespace App\Filament\Resources\Actions;

use App\Models\People;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class LinkImdbAction
{
    static function action(string $endpoint): Action
    {
        return Action::make('linkImdb')
            ->color('primary')
            ->label('Ga naar imdb pagina')
            ->icon('heroicon-m-film')
            ->hidden(function (?Model $record) {
                return $record?->imdb_externid === null;
            })
            ->action(function (?Model $record) use ($endpoint) {
                return redirect('https://www.imdb.com/' . $endpoint . $record->imdb_externid);
            });
    }
}
