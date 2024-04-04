<?php

namespace App\Filament\Widgets\Charts\Genres;

use App\Filament\Widgets\Charts\DefaultChartFilters\GenreFilter;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Widgets\Widget;

class GenreProfitPredictionChart extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.genre-profit-prediction-chart';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                GenreFilter::get()
                    ->label('genre')
                    ->multiple(false),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        dd($this->form->getState());
    }
}
