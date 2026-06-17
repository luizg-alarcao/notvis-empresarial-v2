<?php

namespace App\Livewire\Clientes;

use Livewire\Component;
use App\Models\Cliente;
use Livewire\Attributes\Layout;

class VisualizarCliente extends Component
{
    public Cliente $cliente;
    public $abaAtiva = 'dados'; // Aba padrão

    public function mount(Cliente $cliente)
    {
        $this->cliente = $cliente;
    }

    public function setAba($nomeAba)
    {
        $this->abaAtiva = $nomeAba;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.clientes.visualizar-cliente');
    }
}
