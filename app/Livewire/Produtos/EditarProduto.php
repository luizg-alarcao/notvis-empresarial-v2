<?php

namespace App\Livewire\Produtos;

use Livewire\Component;
use App\Models\Produto;
use Livewire\Attributes\Layout;

class EditarProduto extends Component
{
    public $produtoId;
    public $nome, $descricao_detalhada, $tipo, $marca, $categoria;
    public $codigo_interno, $codigo_barras, $unidade;
    public $preco_custo, $margem_lucro, $preco_venda_vista, $preco_venda_prazo;
    public $estoque_atual, $estoque_minimo, $estoque_maximo;
    public $cfop, $cst_csosn, $origem, $aliquota_icms, $ativo; // Adicionei as que faltavam
    public $abaAtiva = 'geral';

    // AJUSTE AQUI: O parâmetro deve bater com o nome na rota {id}
    public function mount($id)
    {
        $produto = Produto::findOrFail($id);
        $this->produtoId = $id;

        $this->nome = $produto->nome;
        $this->descricao_detalhada = $produto->descricao_detalhada;
        $this->tipo = $produto->tipo;
        $this->marca = $produto->marca;
        $this->categoria = $produto->categoria;
        $this->codigo_interno = $produto->codigo_interno;
        $this->codigo_barras = $produto->codigo_barras;
        $this->unidade = $produto->unidade;
        $this->preco_custo = $produto->preco_custo;
        $this->margem_lucro = $produto->margem_lucro;
        $this->preco_venda_vista = $produto->preco_venda_vista;
        $this->preco_venda_prazo = $produto->preco_venda_prazo;
        $this->estoque_atual = $produto->estoque_atual;
        $this->estoque_minimo = $produto->estoque_minimo;
        $this->estoque_maximo = $produto->estoque_maximo;
        $this->cfop = $produto->cfop;
        $this->cst_csosn = $produto->cst_csosn;
        $this->origem = $produto->origem;
        $this->aliquota_icms = $produto->aliquota_icms;
        $this->ativo = (bool)$produto->ativo; // Garante que o checkbox carregue o estado correto
    }

    public function setAba($aba) { $this->abaAtiva = $aba; }

    public function updatedPrecoCusto($value)
    {
        $this->recalcularPrecos();
    }

    public function updatedMargemLucro($value)
    {
        $this->recalcularPrecos();
    }

    public function updatedPrecoVendaPrazo($value)
    {
        $custo = (float) $this->preco_custo;
        $vendaPrazo = (float) $value;

        if ($custo > 0 && $vendaPrazo > 0) {
            $this->margem_lucro = round((($vendaPrazo - $custo) / $custo) * 100, 2);
            $this->preco_venda_vista = round($vendaPrazo * 0.95, 2);
        }
    }

    private function recalcularPrecos()
    {
        $custo = (float) $this->preco_custo;
        $margem = (float) $this->margem_lucro;

        if ($custo > 0) {
            $this->preco_venda_prazo = round($custo + ($custo * ($margem / 100)), 2);
            $this->preco_venda_vista = round($this->preco_venda_prazo * 0.95, 2);
        }
    }

    public function atualizar()
    {
        $this->validate([
            'nome' => 'required',
            'categoria' => 'required',
            'cfop' => 'required|max:4',
            'cst_csosn' => 'required|max:3',
            'preco_custo' => 'required|numeric|min:0.01',
            'marca' => 'nullable',
        ]);

        // Remove qualquer letra que possa ter vindo no código de barras
        $codigoLimpo = preg_replace('/[^0-9]/', '', $this->codigo_barras);
        $codigoFinal = empty($codigoLimpo) ? null : $codigoLimpo;

        try {
            $produto = Produto::find($this->produtoId);
            $produto->update([
                'nome' => mb_strtoupper($this->nome, 'UTF-8'),
                'descricao_detalhada' => mb_strtoupper($this->descricao_detalhada, 'UTF-8'),
                'tipo' => substr($this->tipo, 0, 1),
                'marca' => $this->marca ? mb_strtoupper($this->marca, 'UTF-8') : null,
                'categoria' => mb_strtoupper($this->categoria, 'UTF-8'),
                'codigo_interno' => $this->codigo_interno ?: null,
                'codigo_barras' => $codigoFinal,
                'unidade' => mb_strtoupper($this->unidade, 'UTF-8'),
                'preco_custo' => (float)$this->preco_custo,
                'margem_lucro' => (float)$this->margem_lucro,
                'preco_venda_vista' => (float)$this->preco_venda_vista,
                'preco_venda_prazo' => (float)$this->preco_venda_prazo,
                'estoque_atual' => (float)$this->estoque_atual,
                'estoque_minimo' => (float)$this->estoque_minimo,
                'estoque_maximo' => (float)$this->estoque_maximo,
                'cfop' => $this->cfop,
                'cst_csosn' => $this->cst_csosn,
                'origem' => (int)$this->origem,
                'aliquota_icms' => (float)$this->aliquota_icms,
                'ativo' => $this->ativo ? 1 : 0
            ]);

            session()->flash('message', 'PRODUTO ATUALIZADO COM SUCESSO!');
            return redirect()->route('produtos.index');
        } catch (\Exception $e) {
            session()->flash('error', 'ERRO AO ATUALIZAR: ' . $e->getMessage());
        }
    }

    #[Layout('layouts.app')]
    public function render() { return view('livewire.produtos.editar-produto'); }
}
