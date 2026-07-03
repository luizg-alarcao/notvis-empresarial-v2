<?php

namespace App\Livewire\Clientes;

use Livewire\Component;
use App\Models\Cliente;
use App\Models\AuditLog;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

class ListarClientes extends Component
{
    use WithPagination;

    public $search = '';

    // Isso aqui faz a paginação resetar quando você pesquisa algo
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function excluir($id)
    {
        $cliente = Cliente::find($id);

        if ($cliente) {
            AuditLog::registrar('clientes', 'cliente_excluido', 'Cliente removido do cadastro.', $cliente, [
                'nome' => $cliente->nome,
                'documento' => $cliente->cpf_cnpj,
            ]);
            $cliente->delete();
            session()->flash('success', 'Cliente removido com sucesso!');
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $clientes = Cliente::where('nome', 'like', '%' . $this->search . '%')
            ->orWhere('cpf_cnpj', 'like', '%' . $this->search . '%')
            ->orderBy('nome', 'asc')
            ->paginate(10);

        return view('livewire.clientes.listar-clientes', [
            'clientes' => $clientes
        ]);
    }
}
