<?php

namespace App\Livewire;

use Livewire\Component;

class GenrePredictionImage extends Component
{
    public string $imageName;

    public function mount(string $imageName)
    {
        $this->imageName = $imageName;
    }

    public function render()
    {
        return view('livewire.genre-prediction-image');
    }


}
