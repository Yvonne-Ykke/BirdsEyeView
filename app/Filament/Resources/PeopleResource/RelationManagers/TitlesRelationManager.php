<?php

namespace App\Filament\Resources\PeopleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TitlesRelationManager extends RelationManager
{
    protected static string $relationship = 'titles';
    protected static ?string $title = 'Gespeeld in';
    protected static ?string $modelLabel = 'Productie';
    protected static ?string $pluralLabel = 'Producties';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('primary_title')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('primary_title')
            ->columns([
                Tables\Columns\TextColumn::make('primary_title')
                    ->label('Titel'),
            ])
            ->filters([
                //
            ])
            ->headerActions([

            ])
            ->actions([

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([

                ]),
            ]);
    }
}
