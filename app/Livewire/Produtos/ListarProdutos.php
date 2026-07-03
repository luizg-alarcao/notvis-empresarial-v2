<?php

namespace App\Livewire\Produtos;

use Livewire\Component;
use App\Models\AuditLog;
use App\Models\Produto;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

class ListarProdutos extends Component
{
    use WithPagination;

    public $busca = '';

    // Reseta a página quando o usuário digita na busca
    public function updatingBusca() { $this->gotoPage(1); }

    public function excluir($id)
    {
        $produto = Produto::find($id);

        if ($produto) {
            AuditLog::registrar('produtos', 'produto_excluido', 'Produto ou servico removido do cadastro.', $produto, [
                'nome' => $produto->nome,
                'tipo' => $produto->tipo,
                'codigo_barras' => $produto->codigo_barras,
            ]);
            $produto->delete();
            session()->flash('success', 'Produto removido com sucesso!');
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $produtos = Produto::where('nome', 'like', '%' . $this->busca . '%')
            ->orWhere('codigo_interno', 'like', '%' . $this->busca . '%')
            ->orWhere('codigo_barras', 'like', '%' . $this->busca . '%')
            ->orderBy('nome', 'asc')
            ->paginate(10);

        return view('livewire.produtos.listar-produtos', [
            'produtos' => $produtos
        ]);
    }
}
