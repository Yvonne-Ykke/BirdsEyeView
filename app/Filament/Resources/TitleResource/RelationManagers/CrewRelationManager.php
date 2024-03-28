<?php

namespace App\Filament\Resources\TitleResource\RelationManagers;

use App\Filament\Resources\PeopleResource\Pages\ViewPeople;
use App\Models\People;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CrewRelationManager extends RelationManager
{
    protected static string $relationship = 'crew';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->recordUrl(
                fn(?People $record): string => ViewPeople::getUrl([$record->id]),
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->label('Naam'),
                Tables\Columns\TextColumn::make('birth_year')
                    ->label('Geboortejaar')
                    ->sortable(),
                Tables\Columns\TextColumn::make('death_year')
                    ->label('Sterfjaar')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gender')
                    ->label('Gender')
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            1 => 'Vrouw',
                            2 => 'Man',
                            3 => 'Non-binair',
                            default => 'Onbekend'
                        };
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([

            ])
            ->actions([

            ])
            ->bulkActions([

            ]);
    }
}
