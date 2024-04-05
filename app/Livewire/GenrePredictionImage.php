<?php

namespace App\Livewire;

use Livewire\Component;

class GenrePredictionImage extends Component
{
    public string $imageName;

    protected $listeners = ['refreshImage'];
    public function mount(string $imageName)
    {
        $this->imageName = $imageName;
    }


    public function refreshImage(): void
    {
        $this->render();
    }

    public function render()
    {
        return view('livewire.genre-prediction-image');
    }


}
