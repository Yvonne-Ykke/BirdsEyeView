<?php

namespace App\Livewire;

use Livewire\Component;

class GenrePredictionImage extends Component
{
    public string $image;

    public function mount(string $image)
    {
        $this->image = $image;
    }

    public function render()
    {
        return view('livewire.genre-prediction-image');
    }


}
