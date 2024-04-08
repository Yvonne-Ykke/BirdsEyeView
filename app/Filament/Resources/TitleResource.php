<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Actions\LinkImdbAction;
use App\Filament\Resources\Actions\LinkTmdbAction;
use App\Filament\Resources\TitleResource\Pages;
use App\Filament\Resources\TitleResource\RelationManagers;
use App\Models\Title;
use App\Support\Enums\TitleTypes;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TitleResource extends Resource
{
    protected static ?string $model = Title::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Overzichten';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Titels';
    protected static ?string $modelLabel = 'Titel';
    protected static ?string $pluralLabel = 'Titels';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Actions::make([
                    LinkImdbAction::action('title/'),
                    LinkTmdbAction::action('movie/'),
                ])->columnSpanFull(),
                Forms\Components\Section::make('Gegevens')
                    ->columnSpan(1)
                    ->icon('heroicon-m-archive-box')
                    ->schema([
                        Forms\Components\TextInput::make('primary_title')
                            ->inlineLabel()
                            ->label('Titel'),
                        Forms\Components\TextInput::make('type')
                            ->inlineLabel()
                            ->formatStateUsing(function ($state) {
                                return TitleTypes::translationArray()[$state];
                            })
                            ->label('Type'),
                        Forms\Components\TextInput::make('start_year')
                            ->inlineLabel()
                            ->label('Uitgebracht op'),
                        Forms\Components\TextInput::make('end_year')
                            ->inlineLabel()
                            ->label('Eindjaar'),
                        Forms\Components\TextInput::make('runtime_minutes')
                            ->inlineLabel()
                            ->suffix('minuten')
                            ->label('Lengte'),
                    ]),
                Forms\Components\Section::make('Genres')
                    ->columnSpan(1)
                    ->icon('heroicon-m-clipboard-document-list')
                    ->schema([
                        Forms\Components\Repeater::make('genres')
                            ->relationship('genres')
//                            ->hiddenLabel()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->hiddenLabel()
                            ]),
                    ]),
                Forms\Components\Section::make('Geld')
                    ->columnSpan(1)
                    ->icon('heroicon-m-currency-euro')
                    ->schema([
                        Forms\Components\TextInput::make('budget')
                            ->inlineLabel()
                            ->label('Budget')
                            ->formatStateUsing(function ($state) {
                                if (($state ?? 0) > 0) {
                                    return ($state / 1000000) . " mln";
                                }
                                return 'Onbekend';
                            }),
                        Forms\Components\TextInput::make('revenue')
                            ->inlineLabel()
                            ->label('Omzet')
                            ->formatStateUsing(function ($state) {
                                if (($state ?? 0) > 0) {
                                    return ($state / 1000000) . " mln";
                                }
                                return 'Onbekend';
                            }),
                        Forms\Components\TextInput::make('profit')
                            ->inlineLabel()
                            ->label('Winst')
                            ->formatStateUsing(function (?Title $record) {
                                if (($state ?? 0) > 0) {
                                    (($record->revenue - $record->budget) / 1000000) . " mln";
                                }
                                return 'Onbekend';
                            }),
                    ]),
                Forms\Components\Section::make('Reviews')
                    ->columnSpan(1)
                    ->relationship('rating')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->schema([
                        Forms\Components\TextInput::make('average_rating')
                            ->inlineLabel()
                            ->formatStateUsing(function ($state) {
                                return round($state, 2);
                            })
                            ->label('Gemiddelde beoordeling'),
                        Forms\Components\TextInput::make('number_votes')
                            ->inlineLabel()
                            ->label('Aantal beoordelingen'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('primary_title')
                    ->label('Titel')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(function ($state) {
                        return TitleTypes::translationArray()[$state];
                    })
                    ->label('Titel'),
                Tables\Columns\TextColumn::make('start_year')
                    ->searchable()
                    ->label('Uitgebracht op'),
                Tables\Columns\TextColumn::make('end_year')
                    ->label('Eindjaar'),
                Tables\Columns\TextColumn::make('budget')
                    ->label('Budget')
                    ->formatStateUsing(function ($state) {
                        return ($state / 1000000) . " mln";
                    }),
                Tables\Columns\TextColumn::make('revenue')
                    ->label('Omzet')
                    ->formatStateUsing(function ($state) {
                        return ($state / 1000000) . " mln";
                    }),
                Tables\Columns\TextColumn::make('profit')
                    ->label('Winst')
                    ->formatStateUsing(function (?Title $record) {
                        return (($record->revenue - $record->budget) / 1000000) . " mln";
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CrewRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTitles::route('/'),
            'view' => Pages\ViewTitle::route('/{record}'),
        ];
    }
}
