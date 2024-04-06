<?php

namespace App\Livewire;

use Livewire\Component;

class BestProductionCompaniesTable extends Component
{
    public array $data = [];

    public function mount($data): void
    {
        $this->data = json_decode(json_encode($data), true);
    }

    public function render()
    {
        return view('livewire.best-production-companies-table');
    }
}
