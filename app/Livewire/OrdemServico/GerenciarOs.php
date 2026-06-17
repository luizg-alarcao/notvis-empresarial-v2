<?php

namespace App\Livewire\OrdemServico;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Schema;
use App\Models\OrdemServico;
use App\Models\OsItem;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Servico;

class GerenciarOs extends Component
{
    public OrdemServico $os;

    // Propriedades do Formulário
    public $nome_cartao;
    public $atendente_id;
    public $cliente_id;
    public $nome_cliente;
    public $placa_veiculo;
    public $marca_modelo_veiculo;
    public $km_veiculo;
    public $sintoma_reclamacao;

    // Modais e Buscas
    public $modalClienteAberto = false;
    public $modalProdutoAberto = false;
    public $pesquisaCliente = '';
    public $pesquisaProduto = '';

    // Campo Híbrido de Inserção (Bipagem / Digitação)
    public $buscaProdutoOuCodigo = '';
    public $resultadosFiltradosInline = [];

    // Totais do Rodapé
    public $desconto_porcento = 0;
    public $desconto_reais = 0;
    public $subtotal = 0;
    public $total_geral = 0;

    // Seleção e edição rápida
    public $itemSelecionadoId = null;

    // Mantido e tipado dinamicamente para evitar conflito de hidratação no Livewire v3
    public $itensDaOs = [];

    public $editandoItemId = null;
    public $novoNomeItem = '';

    public function mount($id = null)
    {
        if ($id) {
            $this->os = OrdemServico::findOrFail($id);
        } else {
            $novaOs = OrdemServico::create([
                'status' => 'RASCUNHO',
                'nome_cartao' => '',
                'cliente_id' => 31
            ]);
            return redirect()->route('os.editar', $novaOs->id);
        }

        $this->nome_cartao          = $this->os->nome_cartao ?? '';
        $this->placa_veiculo        = $this->os->placa_veiculo;
        $this->marca_modelo_veiculo = $this->os->marca_modelo_veiculo;
        $this->km_veiculo           = $this->os->km_veiculo;
        $this->sintoma_reclamacao   = $this->os->sintoma_reclamacao;
        $this->atendente_id         = $this->os->atendente_id;
        $this->cliente_id           = $this->os->cliente_id;
        $this->nome_cliente         = $this->os->cliente?->nome ?? 'CONSUMIDOR';

        $this->atualizarTotaisOs();
    }

    // --- SEÇÃO CLIENTES (DIGITAÇÃO DIRETA DO ID + AUTOCOMPLETE) ---
    public function updatedClienteId($value)
    {
        if ($value) {
            $cliente = Cliente::find($value);
            if ($cliente) {
                $this->nome_cliente = $cliente->nome;
                $this->salvarCampo('cliente_id');
            } else {
                $this->nome_cliente = 'CLIENTE NÃO ENCONTRADO';
            }
        } else {
            $this->nome_cliente = '';
        }
    }

    public function updatedNomeCliente()
    {
        if (strlen($this->nome_cliente) > 1) {
            $this->resultadosFiltradosInline = Cliente::where('nome', 'like', '%' . $this->nome_cliente . '%')
                ->take(5)->get()->toArray();
        } else {
            $this->resultadosFiltradosInline = [];
        }
    }

    public function selecionarClienteDirect($id, $nome)
    {
        $this->cliente_id = $id;
        $this->nome_cliente = $nome;
        $this->salvarCampo('cliente_id');
        $this->resultadosFiltradosInline = [];
    }

    public function abrirModalCliente()
    {
        $this->pesquisaCliente = '';
        $this->modalClienteAberto = true;
    }

    // --- SEÇÃO PRODUTOS ---
    public function processarInsercaoRapida()
    {
        $termo = trim($this->buscaProdutoOuCodigo);
        if (!$termo) return;

        $produto = Produto::where('id', $termo)
            ->orWhere('codigo_barras', $termo)
            ->orWhere('sku', $termo)
            ->first();

        if ($produto) {
            $precoPadrao = $produto->preco_unitario ?? $produto->preco_venda_vista ?? 0;
            $this->adicionarItemDireto($produto->id, 'produto', $produto->nome, $precoPadrao);
            $this->buscaProdutoOuCodigo = '';
            return;
        }

        $servico = Servico::find($termo);
        if ($servico) {
            $precoPadrao = $servico->preco ?? $servico->valor ?? $servico->valor_base ?? 0;
            $this->adicionarItemDireto($servico->id, 'servico', $servico->descricao, $precoPadrao);
            $this->buscaProdutoOuCodigo = '';
            return;
        }

        $this->pesquisaProduto = $termo;
        $this->buscaProdutoOuCodigo = '';
        $this->modalProdutoAberto = true;
    }

    public function abrirModalProduto()
    {
        $this->pesquisaProduto = '';
        $this->modalProdutoAberto = true;
    }

    public function adicionarItem($id, $tipo, $fecharModal = false)
    {
        if ($tipo === 'produto') {
            $produto = Produto::find($id);
            if (!$produto || $produto->estoque_atual < 1) {
                session()->flash('error', 'Produto sem estoque disponível!');
                return;
            }
            $precoUn = $produto->preco_unitario ?? $produto->preco_venda_vista ?? 0;
            $descricao = $produto->nome;
        } else {
            $servico = Servico::find($id);
            if (!$servico) return;
            $precoUn = $servico->preco ?? $servico->valor ?? 0;
            $descricao = $servico->descricao;
        }

        // Correção de busca ativa para evitar coluna inexistente 'servico_id'
        $itemExistente = OsItem::where('ordem_servico_id', $this->os->id)
            ->where('tipo', $tipo === 'produto' ? 'PECA' : 'SERVICO')
            ->where(function($query) use ($tipo, $id, $descricao) {
                if ($tipo === 'produto') {
                    $query->where('produto_id', $id);
                } else {
                    $query->where('descricao', $descricao);
                }
            })
            ->first();

        if ($itemExistente) {
            $itemExistente->quantidade += 1;
            $itemExistente->valor_total = ($itemExistente->quantidade * $itemExistente->valor_unitario) - ($itemExistente->desconto_valor ?? 0);
            $itemExistente->save();
        } else {
            OsItem::create([
                'ordem_servico_id' => $this->os->id,
                'tipo'             => $tipo === 'produto' ? 'PECA' : 'SERVICO',
                'produto_id'       => $tipo === 'produto' ? $id : null,
                'descricao'        => $descricao,
                'quantidade'       => 1,
                'valor_unitario'   => $precoUn,
                'desconto_tipo'    => null,
                'desconto_valor'   => 0,
                'valor_total'      => $precoUn,
            ]);
        }

        if ($tipo === 'produto' && isset($produto)) {
            $produto->decrement('estoque_atual', 1);
        }

        if ($fecharModal) {
            $this->modalProdutoAberto = false;
        }

        $this->atualizarTotaisOs();
    }

    public function adicionarItemDireto($id, $tipo, $descricao, $preco)
    {
        if ($tipo === 'produto') {
            $produto = Produto::find($id);
            if (!$produto || (int) $produto->estoque_atual < 1) {
                return;
            }
        }

        // Correção de busca ativa para evitar coluna inexistente 'servico_id'
        $itemExistente = OsItem::where('ordem_servico_id', $this->os->id)
            ->where('tipo', $tipo === 'produto' ? 'PECA' : 'SERVICO')
            ->where(function($query) use ($tipo, $id, $descricao) {
                if ($tipo === 'produto') {
                    $query->where('produto_id', $id);
                } else {
                    $query->where('descricao', $descricao);
                }
            })
            ->first();

        if ($itemExistente) {
            $itemExistente->quantidade += 1;
            $itemExistente->valor_unitario = (float) $itemExistente->valor_unitario;
            $itemExistente->desconto_valor = (float) ($itemExistente->desconto_valor ?? 0);
            $itemExistente->valor_total = ($itemExistente->quantidade * $itemExistente->valor_unitario) - $itemExistente->desconto_valor;

            $itemExistente->save();
        } else {
            $dadosItem = [
                'ordem_servico_id' => $this->os->id,
                'tipo'              => $tipo === 'produto' ? 'PECA' : 'SERVICO',
                'descricao'         => mb_strtoupper((string) $descricao, 'UTF-8'),
                'quantidade'        => 1,
                'valor_unitario'    => (float) $preco,
                'desconto_tipo'     => null,
                'desconto_valor'    => 0,
                'valor_total'       => (float) $preco,
                'produto_id'        => $tipo === 'produto' ? $id : null,
            ];

            OsItem::create($dadosItem);
        }

        if ($tipo === 'produto' && isset($produto)) {
            $produto->decrement('estoque_atual', 1);
        }

        $this->atualizarTotaisOs();
    }

    public function alterarQuantidade($itemId, $qtd)
    {
        $item = OsItem::find($itemId);
        if ($item && $qtd > 0) {
            if ($item->produto_id) {
                $produto = Produto::find($item->produto_id);
                if ($produto) {
                    $diferenca = (float)$qtd - $item->quantidade;
                    $produto->decrement('estoque_atual', $diferenca);
                }
            }

            $item->quantidade = (float) $qtd;
            $item->desconto_valor = (float) ($item->desconto_valor ?? 0);
            $item->valor_total = ($item->quantidade * (float) $item->valor_unitario) - $item->desconto_valor;

            $item->save();
            $this->atualizarTotaisOs();
        }
    }

    public function alterarDescontoItem($itemId, $desconto)
    {
        $item = OsItem::find($itemId);
        if ($item && $desconto >= 0) {
            $item->desconto_valor = (float) $desconto;
            $item->valor_total = ((float) $item->quantidade * (float) $item->valor_unitario) - $item->desconto_valor;

            $item->save();
            $this->atualizarTotaisOs();
        }
    }

    public function alterarPrecoUnitarioBanco($itemId, $novoPreco)
    {
        $item = OsItem::find($itemId);
        if ($item && $novoPreco >= 0) {
            $item->valor_unitario = (float) $novoPreco;
            $item->desconto_valor = (float) ($item->desconto_valor ?? 0);
            $item->valor_total = ((float) $item->quantidade * (float) $novoPreco) - $item->desconto_valor;

            $item->save();

            if ($item->produto_id) {
                $prod = Produto::find($item->produto_id);
                if ($prod) {
                    $prod->preco_unitario = $novoPreco;
                    $prod->save();
                }
            } elseif ($item->tipo === 'SERVICO') {
                $serv = Servico::where('descricao', $item->descricao)->first();
                if ($serv) {
                    $serv->preco = $novoPreco;
                    $serv->save();
                }
            }

            $this->atualizarTotaisOs();
        }
    }

    public function atualizarCampo($itemId, $campo, $valor)
    {
        $item = OsItem::find($itemId);

        if ($item) {
            if (in_array($campo, ['valor_unitario', 'desconto_valor', 'quantidade'])) {
                $valor = str_replace('.', '', (string) $valor);
                $valor = str_replace(',', '.', $valor);
                $valor = (float) $valor;
            }

            $item->$campo = $valor;

            if ($campo === 'quantidade' && $item->produto_id) {
                $novaQtd = (float) $item->quantidade;
                $diferenca = $novaQtd - $item->getOriginal('quantidade');

                $produto = Produto::find($item->produto_id);
                if ($produto) {
                    $produto->decrement('estoque_atual', $diferenca);
                }
            }

            $totalBrutoItem = $item->quantidade * $item->valor_unitario;
            $descontoItem = 0;

            if ((float) ($item->desconto_valor ?? 0) > 0) {
                if (($item->desconto_tipo ?? 'VALOR') === 'PORCENTAGEM') {
                    $descontoItem = $totalBrutoItem * ((float) $item->desconto_valor / 100);
                } else {
                    $descontoItem = (float) $item->desconto_valor;
                }
            }

            $item->valor_total = max(0, $totalBrutoItem - $descontoItem);
            $item->save();
        }

        $this->atualizarTotaisOs();
    }

    public function excluirItem($itemId)
    {
        $item = OsItem::find($itemId);
        if ($item) {
            if ($item->produto_id) {
                $produto = Produto::find($item->produto_id);
                if ($produto) {
                    $produto->increment('estoque_atual', $item->quantidade);
                }
            }
            $item->delete();
        }

        if ($this->itemSelecionadoId == $itemId) {
            $this->itemSelecionadoId = null;
        }

        $this->atualizarTotaisOs();
    }

    public function removerItem($itemId)
    {
        $this->excluirItem($itemId);
    }

    public function calcularTotais()
    {
        $this->atualizarTotaisOs();
    }

    public function recalcularGeral()
    {
        $this->atualizarTotaisOs();
    }

    public function atualizarTotaisOs()
    {
        $this->os->refresh();
        $itens = OsItem::where('ordem_servico_id', $this->os->id)->get();

        $totalPecas = (float) $itens->where('tipo', 'PECA')->sum('valor_total');
        $totalServicos = (float) $itens->where('tipo', 'SERVICO')->sum('valor_total');

        $subtotalGeral = $totalPecas + $totalServicos;

        $descontoGeral = 0;
        $descontoGeralValorInput = (float) ($this->os->desconto_geral_valor ?? 0);

        if ($descontoGeralValorInput > 0) {
            if (($this->os->desconto_geral_tipo ?? 'VALOR') === 'PORCENTAGEM') {
                $descontoGeral = $subtotalGeral * ($descontoGeralValorInput / 100);
            } else {
                $descontoGeral = $descontoGeralValorInput;
            }
        }

        $valorLiquido = max(0, $subtotalGeral - $descontoGeral);

        $this->subtotal = $subtotalGeral;
        $this->desconto_reais = $descontoGeral;
        $this->total_geral = $valorLiquido;

        $this->os->update([
            'valor_total_pecas' => $totalPecas,
            'valor_total_servicos' => $totalServicos,
            'valor_total_liquido' => $valorLiquido,
        ]);

        $this->os->refresh();
    }

    public function selecionarItem($id)
    {
        $this->itemSelecionadoId = $this->itemSelecionadoId == $id ? null : $id;
    }

    public function iniciarEdicao($id, $nomeAtual)
    {
        $this->editandoItemId = $id;
        $this->novoNomeItem = (string) $nomeAtual;
    }

    public function salvarNome()
    {
        if ($this->editandoItemId) {
            $item = OsItem::find($this->editandoItemId);
            if ($item) {
                $item->descricao = mb_strtoupper(trim((string) $this->novoNomeItem), 'UTF-8');
                $item->save();
            }
            $this->editandoItemId = null;
            $this->novoNomeItem = '';
            $this->atualizarTotaisOs();
        }
    }

    public function aplicarDescontoGeral()
    {
        $this->atualizarTotaisOs();
    }

    public function salvarCampo($campo)
    {
        $camposTexto = ['placa_veiculo', 'marca_modelo_veiculo', 'km_veiculo', 'sintoma_reclamacao', 'nome_cartao'];
        if (in_array($campo, $camposTexto)) {
            $this->$campo = mb_strtoupper(trim((string) $this->$campo), 'UTF-8');
        }

        $osBanco = OrdemServico::find($this->os->id);
        if ($osBanco) {
            $osBanco->{$campo} = $this->$campo;
            $osBanco->save();
        }
    }

    public function alternarCartao($id) { return redirect()->route('os.editar', $id); }

    public function novoAtendimento($nome = '') {
        $novaOs = OrdemServico::create(['status' => 'RASCUNHO', 'nome_cartao' => mb_strtoupper(trim((string) $nome), 'UTF-8'), 'cliente_id' => 31]);
        return redirect()->route('os.editar', $novaOs->id);
    }

    public function excluirCartao() {
        $idDeletado = $this->os->id; $this->os->delete();
        $proximaOs = OrdemServico::where('id', '!=', $idDeletado)->where('status', 'RASCUNHO')->latest()->first();
        return redirect()->route('os.editar', $proximaOs?->id ?? null);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $listaCartoes = OrdemServico::where('status', 'RASCUNHO')->orderBy('id', 'desc')->get();

        // SINCRONISMO SÊNIOR: Alimenta a propriedade pública antes da renderização para blindar a Blade contra erros
        $this->itensDaOs = OsItem::where('ordem_servico_id', $this->os->id)->get();

        $listaClientesModal = $this->modalClienteAberto ? Cliente::where('nome', 'like', '%' . $this->pesquisaCliente . '%')->take(10)->get() : [];
        $produtosModal = $this->modalProdutoAberto ? Produto::where('nome', 'like', '%' . $this->pesquisaProduto . '%')->take(10)->get() : [];
        $servicosModal = $this->modalProdutoAberto ? Servico::where('descricao', 'like', '%' . $this->pesquisaProduto . '%')->take(10)->get() : [];

        return view('livewire.ordem-servico.gerenciar-os', [
            'listaCartoes' => $listaCartoes,
            'itensDaOs' => $this->itensDaOs,
            'listaClientesModal' => $listaClientesModal,
            'produtosModal' => $produtosModal,
            'servicosModal' => $servicosModal
        ]);
    }
}
