<?php

namespace App\Livewire;

use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        // Aqui definimos que ele vai usar o seu layout principal (app.blade.php)
        return view('livewire.home')->layout('layouts.app');
    }
}
