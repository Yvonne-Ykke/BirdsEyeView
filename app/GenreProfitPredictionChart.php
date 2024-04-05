<?php

namespace App\Filament\Widgets\Charts\Genres;

use App\Filament\Widgets\DefaultFilters\GenreFilter;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Widget;
use Filament\Forms\Form;
use Exception;

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

        $command = 'cd scripts/R && ';
        $command .= 'Rscript -e "renv::restore()" && ';
        $command .= 'Rscript genre-predictions.R';

        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);

        // Check if there was an error executing the command
        if ($return_var !== 0) {
            throw new Exception("Error executing the R script");
        }

        // // Output of the command
        // $csvContent = implode("\n", $output);
        
    }
}
