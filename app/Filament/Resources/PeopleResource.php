<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Actions\LinkImdbAction;
use App\Filament\Resources\Actions\LinkTmdbAction;
use App\Filament\Resources\PeopleResource\Pages;
use App\Filament\Resources\PeopleResource\RelationManagers;
use App\Models\People;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PeopleResource extends Resource
{
    protected static ?string $model = People::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Overzichten';
    protected static ?string $modelLabel = 'Persoon';
    protected static ?string $pluralModelLabel = 'Personen';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Actions::make([
                    LinkImdbAction::action(),
                    LinkTmdbAction::action(),
                ])->columnSpanFull(),
                Forms\Components\Section::make('Gegevens')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Naam')
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('birth_year')
                            ->label('Geboortejaar')
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('death_year')
                            ->label('Sterfjaar')
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('gender')
                            ->label('Sterfjaar')
                            ->formatStateUsing(function ($state) {
                                return match ($state) {
                                    1 => 'Vrouw',
                                    2 => 'Man',
                                    3 => 'Non-binair',
                                    default => 'Onbekend'
                                };
                            })
                            ->inlineLabel(),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('birth_year')
                    ->label('Geboortejaar')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('death_year')
                    ->label('Sterfjaar')
                    ->searchable()
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
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->headerActions([

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProfessionsRelationManager::class,
            RelationManagers\TitlesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeople::route('/'),
            'view' => Pages\ViewPeople::route('/{record}')
        ];
    }
}
