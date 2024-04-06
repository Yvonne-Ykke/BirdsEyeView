<?php

namespace App\Filament\Widgets\Charts\Genres;

use App\Filament\Widgets\Charts\DefaultChartFilters\GenreFilter;
use App\Filament\Widgets\Charts\DefaultChartFilters\YearFromFilter;
use App\Support\Actions\FindOrCreateStorageDirectory;
use Filament\Forms\Concerns\InteractsWithForms;
use Symfony\Component\Process\Process;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Widget;
use Filament\Forms\Form;
use Exception;

use function Termwind\render;

class GenreProfitPredictionChart extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.genre-profit-prediction-chart';

    protected $listeners = ['refreshImage' => '$refresh'];

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
                    ->label('Genre')
                    ->multiple(false)
                    ->required(),
                YearFromFilter::get()
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $path = storage_path('app/public/r');

        app(FindOrCreateStorageDirectory::class)('public/r');
        $process = new Process([
            'Rscript',
            base_path('scripts/R/genre-predictions.R'),
            $path,
            $this->data['genres'],
            $this->data['yearFrom'],
        ]);

        $process->run();

        // Executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new Exception($process->getErrorOutput());
        }

    //    $this->emit('refreshImage', 'randomtest.png');
    }
}
