<?php

// namespace App\Livewire;
namespace App\Http\Livewire;

use Livewire\Component;

class ExampleComponent extends Component
{
    public $count1 = 0;

    public function increment()
    {
        $this->count1++;
    }

    public function render()
    {
        return view('livewire.example-component');
    }
}
