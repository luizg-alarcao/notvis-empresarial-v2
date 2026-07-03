<?php

namespace App\Livewire\OrdemServico;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\OrdemServico;
use App\Models\OsItem;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\AuditLog;
use App\Models\Produto;
use App\Models\Servico;
use App\Services\EstoqueMovimentacaoService;

class GerenciarOs extends Component
{
    public ?OrdemServico $os = null;

    // Propriedades do Formulário
    public $nome_cartao;
    public $atendente_id;
    public $cliente_id;
    public $nome_cliente;
    public $placa_veiculo;
    public $marca_modelo_veiculo;
    public $km_veiculo;
    public $sintoma_reclamacao;
    public $forma_pagamento;

    // Modais e Buscas
    public $modalClienteAberto = false;
    public $modalProdutoAberto = false;
    public $modalServicoManualAberto = false;
    public $modalFinalizacaoAberto = false;
    public $modalCartoesFechadosAberto = false;
    public $modalDevolucaoItemAberto = false;
    public $modalCancelamentoVendaAberto = false;
    public $etapaFinalizacao = 1;
    public $pesquisaCliente = '';
    public $filtroClienteSituacao = 'todos';
    public $clienteHistoricoId = null;
    public $pesquisaProduto = '';
    public $servico_manual_descricao = '';
    public $servico_manual_valor = '';
    public $data_vencimento;
    public $status_pagamento = 'PENDENTE';
    public $observacao_fechamento = '';
    public $cartaoFechadoSelecionadoId = null;
    public $fechados_busca = '';
    public $fechados_empresa = '';
    public $fechados_cliente = '';
    public $fechados_placa = '';
    public $fechados_produto = '';
    public $fechados_data_inicio = '';
    public $fechados_data_fim = '';
    public $fechados_status = 'FINALIZADO';
    public $fechados_pagamento = '';
    public $devolucao_item_id = null;
    public $devolucao_quantidade = '';
    public $devolucao_motivo = '';
    public $motivo_cancelamento = '';

    // Campo Híbrido de Inserção (Bipagem / Digitação)
    public $buscaProdutoOuCodigo = '';
    public $resultadosFiltradosInline = [];

    // Totais do Rodapé
    public $desconto_porcento = 0;
    public $desconto_reais = 0;
    public $desconto_alvo = 'total';
    public $tipo_preco_produto = 'prazo';
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
            $os = OrdemServico::findOrFail($id);

            if ($os->status !== 'RASCUNHO') {
                $proximaOs = OrdemServico::where('status', 'RASCUNHO')
                    ->where('id', '!=', $os->id)
                    ->latest()
                    ->first();

                return $proximaOs
                    ? redirect()->route('os.editar', $proximaOs->id)
                    : redirect()->route('os.nova');
            }

            $this->os = $os;
            $this->carregarDadosDaOs();
        }

        if (request()->boolean('cartoes_fechados')) {
            $this->abrirCartoesFechados();
        }
    }

    private function carregarDadosDaOs(): void
    {
        $this->nome_cartao          = $this->os->nome_cartao ?? '';
        $this->placa_veiculo        = $this->os->placa_veiculo;
        $this->marca_modelo_veiculo = $this->os->marca_modelo_veiculo;
        $this->km_veiculo           = $this->os->km_veiculo;
        $this->sintoma_reclamacao   = $this->os->sintoma_reclamacao;
        $this->forma_pagamento      = $this->os->forma_pagamento;
        $this->data_vencimento      = $this->os->data_vencimento ?? now()->addDays(30)->toDateString();
        $this->status_pagamento     = $this->os->status_pagamento ?? 'PENDENTE';
        $this->observacao_fechamento = $this->os->observacao_fechamento ?? '';
        $this->atendente_id         = $this->os->atendente_id;
        $this->cliente_id           = $this->os->cliente_id;
        $this->nome_cliente         = $this->os->cliente?->nome ?? 'CONSUMIDOR';
        $this->desconto_porcento    = ($this->os->desconto_geral_tipo ?? null) === 'PORCENTAGEM' ? $this->os->desconto_geral_valor : 0;
        $this->desconto_reais       = ($this->os->desconto_geral_tipo ?? null) === 'VALOR' ? $this->os->desconto_geral_valor : 0;

        $this->atualizarTotaisOs();
    }

    public function garantirOsCriada()
    {
        if ($this->os) {
            return;
        }

        $this->os = OrdemServico::create([
            'status' => 'RASCUNHO',
            'nome_cartao' => mb_strtoupper(trim((string) $this->nome_cartao), 'UTF-8'),
            'cliente_id' => null,
        ]);

        $this->salvarCamposPreenchidos();
        $this->atualizarTotaisOs();
    }

    private function salvarCamposPreenchidos(): void
    {
        if (!$this->os) {
            return;
        }

        $dados = [];
        foreach (['nome_cartao', 'atendente_id', 'cliente_id', 'placa_veiculo', 'marca_modelo_veiculo', 'km_veiculo', 'sintoma_reclamacao', 'forma_pagamento', 'data_vencimento', 'status_pagamento', 'observacao_fechamento'] as $campo) {
            if ($this->$campo !== null && $this->$campo !== '') {
                $dados[$campo] = $this->$campo;
            }
        }

        if ($dados) {
            $this->os->update($dados);
            $this->os->refresh();
        }
    }

    // --- SEÇÃO CLIENTES (DIGITAÇÃO DIRETA DO ID + AUTOCOMPLETE) ---
    public function updatedClienteId($value)
    {
        if ($value) {
            $this->garantirOsCriada();
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
            $this->garantirOsCriada();
            $this->resultadosFiltradosInline = Cliente::where('nome', 'like', '%' . $this->nome_cliente . '%')
                ->take(5)->get()->toArray();
        } else {
            $this->resultadosFiltradosInline = [];
        }
    }

    public function selecionarClienteDirect($id, $nome)
    {
        $this->garantirOsCriada();
        $this->cliente_id = $id;
        $this->nome_cliente = $nome;
        $this->salvarCampo('cliente_id');
        $this->resultadosFiltradosInline = [];
        $this->modalClienteAberto = false;
        $this->clienteHistoricoId = null;
    }

    public function abrirModalCliente()
    {
        $this->garantirOsCriada();
        $this->pesquisaCliente = '';
        $this->filtroClienteSituacao = 'todos';
        $this->clienteHistoricoId = null;
        $this->modalClienteAberto = true;
    }

    public function verHistoricoCliente($id)
    {
        $this->clienteHistoricoId = $id;
    }

    public function limparFiltrosCliente()
    {
        $this->pesquisaCliente = '';
        $this->filtroClienteSituacao = 'todos';
        $this->clienteHistoricoId = null;
    }

    // --- SEÇÃO PRODUTOS ---
    public function processarInsercaoRapida()
    {
        $termo = trim($this->buscaProdutoOuCodigo);
        if (!$termo) return;
        $this->garantirOsCriada();

        if ($termo === '2') {
            $this->abrirModalServicoManual();
            $this->buscaProdutoOuCodigo = '';
            return;
        }

        $produto = $this->buscarProdutoPorCodigo($termo);

        if ($produto) {
            $this->adicionarItemDireto($produto->id, 'produto', $produto->nome, null, $this->tipo_preco_produto);
            $this->buscaProdutoOuCodigo = '';
            return;
        }

        $servico = Servico::find($termo);
        if ($servico) {
            $precoPadrao = $servico->preco ?? $servico->valor ?? $servico->valor_base ?? 0;
            $this->adicionarItemDireto($servico->id, 'servico', $servico->descricao ?? $servico->nome, $precoPadrao);
            $this->buscaProdutoOuCodigo = '';
            return;
        }

        $this->pesquisaProduto = $termo;
        $this->buscaProdutoOuCodigo = '';
        $this->modalProdutoAberto = true;
    }

    private function buscarProdutoPorCodigo(string $termo): ?Produto
    {
        $termo = trim($termo);
        $codigoNumerico = preg_replace('/[^0-9]/', '', $termo);
        $variantes = collect([$termo, $codigoNumerico])
            ->filter()
            ->map(fn ($codigo) => trim((string) $codigo))
            ->unique()
            ->values();

        return Produto::query()
            ->where(function ($query) use ($termo, $codigoNumerico, $variantes) {
                if (ctype_digit($termo) && strlen($termo) <= 8) {
                    $query->orWhere('id', (int) $termo);
                }

                foreach ($variantes as $codigo) {
                    $query->orWhere('codigo_barras', $codigo)
                        ->orWhere('codigo_interno', $codigo);
                }

                if ($codigoNumerico && strlen($codigoNumerico) > 1) {
                    $query->orWhere('codigo_barras', ltrim($codigoNumerico, '0'))
                        ->orWhere('codigo_interno', ltrim($codigoNumerico, '0'));
                }
            })
            ->first();
    }

    public function abrirModalProduto()
    {
        $this->garantirOsCriada();
        $this->pesquisaProduto = '';
        $this->modalProdutoAberto = true;
    }

    public function abrirModalServicoManual($descricao = '')
    {
        $this->garantirOsCriada();
        $this->servico_manual_descricao = mb_strtoupper(trim((string) $descricao), 'UTF-8');
        $this->servico_manual_valor = '';
        $this->modalServicoManualAberto = true;
    }

    public function adicionarServicoManual()
    {
        $this->garantirOsCriada();

        $descricao = mb_strtoupper(trim((string) $this->servico_manual_descricao), 'UTF-8');
        $valor = $this->normalizarNumero($this->servico_manual_valor);

        if (strlen($descricao) < 3) {
            $this->addError('servico_manual_descricao', 'Informe o nome do servico.');
            return;
        }

        if ($valor <= 0) {
            $this->addError('servico_manual_valor', 'Informe o valor do servico.');
            return;
        }

        OsItem::create([
            'ordem_servico_id' => $this->os->id,
            'tipo' => 'SERVICO',
            'produto_id' => null,
            'descricao' => $descricao,
            'quantidade' => 1,
            'valor_unitario' => $valor,
            'desconto_tipo' => null,
            'desconto_valor' => 0,
            'valor_total' => $valor,
        ]);

        $this->modalServicoManualAberto = false;
        $this->servico_manual_descricao = '';
        $this->servico_manual_valor = '';
        $this->resetErrorBag(['servico_manual_descricao', 'servico_manual_valor']);
        $this->atualizarTotaisOs();
    }

    public function adicionarItem($id, $tipo, $fecharModal = false, $tipoPreco = null)
    {
        $this->garantirOsCriada();
        $tipoPreco = $tipoPreco ?: $this->tipo_preco_produto;
        $tipoItem = 'SERVICO';
        $produtoIdItem = null;
        $controlaEstoque = false;

        if ($tipo === 'produto') {
            $produto = Produto::find($id);
            if (!$produto) {
                session()->flash('error', 'Produto nao encontrado no cadastro.');
                return;
            }
            $produtoEhServico = $this->produtoCadastradoComoServico($produto);
            $controlaEstoque = $this->produtoControlaEstoque($produto);

            if ($controlaEstoque && (float) $produto->estoque_atual < 1) {
                session()->flash('error', 'Produto sem estoque disponivel!');
                return;
            }

            $precoUn = $this->precoProdutoParaOs($produto, $tipoPreco);
            $descricao = $produto->nome;
            $tipoItem = $produtoEhServico ? 'SERVICO' : 'PECA';
            $produtoIdItem = $produtoEhServico ? null : $produto->id;
        } else {
            $servico = Servico::find($id);
            if (!$servico) return;
            $precoUn = $servico->preco ?? $servico->valor ?? $servico->valor_base ?? 0;
            $descricao = $servico->descricao ?? $servico->nome;
        }

        // Correção de busca ativa para evitar coluna inexistente 'servico_id'
        $itemExistente = OsItem::where('ordem_servico_id', $this->os->id)
            ->where('tipo', $tipoItem)
            ->where(function($query) use ($produtoIdItem, $descricao) {
                if ($produtoIdItem) {
                    $query->where('produto_id', $produtoIdItem);
                } else {
                    $query->where('descricao', $descricao);
                }
            })
            ->where('valor_unitario', $precoUn)
            ->first();

        if ($itemExistente) {
            $itemExistente->quantidade += 1;
            $itemExistente->valor_total = max(0, ($itemExistente->quantidade * $itemExistente->valor_unitario) - ($itemExistente->desconto_valor ?? 0));
            $itemExistente->save();
            $itemMovimentado = $itemExistente;
        } else {
            $itemMovimentado = OsItem::create([
                'ordem_servico_id' => $this->os->id,
                'tipo'             => $tipoItem,
                'produto_id'       => $produtoIdItem,
                'descricao'        => $descricao,
                'quantidade'       => 1,
                'valor_unitario'   => $precoUn,
                'desconto_tipo'    => null,
                'desconto_valor'   => 0,
                'valor_total'      => $precoUn,
            ]);
        }

        if ($tipo === 'produto' && isset($produto) && $controlaEstoque) {
            if (!$this->registrarMovimentoEstoque($produto, 'SAIDA', 1, 'ITEM ADICIONADO NA OS #' . $this->os->id, $itemMovimentado)) {
                return;
            }
        }

        if ($fecharModal) {
            $this->modalProdutoAberto = false;
        }

        $this->atualizarTotaisOs();
    }

    public function adicionarItemDireto($id, $tipo, $descricao, $preco = null, $tipoPreco = null)
    {
        $this->garantirOsCriada();
        $tipoPreco = $tipoPreco ?: $this->tipo_preco_produto;
        $tipoItem = $tipo === 'produto' ? 'PECA' : 'SERVICO';
        $produtoIdItem = null;
        $controlaEstoque = false;

        if ($tipo === 'produto') {
            $produto = Produto::find($id);
            if (!$produto) {
                session()->flash('error', 'Produto nao encontrado no cadastro.');
                return;
            }

            $produtoEhServico = $this->produtoCadastradoComoServico($produto);
            $controlaEstoque = $this->produtoControlaEstoque($produto);

            if ($controlaEstoque && (float) $produto->estoque_atual < 1) {
                session()->flash('error', 'Produto sem estoque disponivel para adicionar na OS.');
                return;
            }

            $preco = $this->precoProdutoParaOs($produto, $tipoPreco);
            $descricao = $produto->nome;
            $tipoItem = $produtoEhServico ? 'SERVICO' : 'PECA';
            $produtoIdItem = $produtoEhServico ? null : $produto->id;
        }

        // Correção de busca ativa para evitar coluna inexistente 'servico_id'
        $itemExistente = OsItem::where('ordem_servico_id', $this->os->id)
            ->where('tipo', $tipoItem)
            ->where(function($query) use ($produtoIdItem, $descricao) {
                if ($produtoIdItem) {
                    $query->where('produto_id', $produtoIdItem);
                } else {
                    $query->where('descricao', $descricao);
                }
            })
            ->where('valor_unitario', (float) $preco)
            ->first();

        if ($itemExistente) {
            $itemExistente->quantidade += 1;
            $itemExistente->valor_unitario = (float) $itemExistente->valor_unitario;
            $itemExistente->desconto_valor = (float) ($itemExistente->desconto_valor ?? 0);
            $itemExistente->valor_total = max(0, ($itemExistente->quantidade * $itemExistente->valor_unitario) - $itemExistente->desconto_valor);

            $itemExistente->save();
            $itemMovimentado = $itemExistente;
        } else {
            $dadosItem = [
                'ordem_servico_id' => $this->os->id,
                'tipo'              => $tipoItem,
                'descricao'         => mb_strtoupper((string) $descricao, 'UTF-8'),
                'quantidade'        => 1,
                'valor_unitario'    => (float) $preco,
                'desconto_tipo'     => null,
                'desconto_valor'    => 0,
                'valor_total'       => (float) $preco,
                'produto_id'        => $produtoIdItem,
            ];

            $itemMovimentado = OsItem::create($dadosItem);
        }

        if ($tipo === 'produto' && isset($produto) && $controlaEstoque) {
            if (!$this->registrarMovimentoEstoque($produto, 'SAIDA', 1, 'ITEM ADICIONADO NA OS #' . $this->os->id, $itemMovimentado)) {
                return;
            }
        }

        $this->atualizarTotaisOs();
    }

    private function produtoCadastradoComoServico(Produto $produto): bool
    {
        $tipo = mb_strtoupper((string) ($produto->tipo ?? 'P'), 'UTF-8');

        return in_array($tipo, ['S', 'SERVICO', 'SERVIÇO'], true);
    }

    private function produtoControlaEstoque(Produto $produto): bool
    {
        return !$this->produtoCadastradoComoServico($produto)
            && (bool) ($produto->controla_estoque ?? true);
    }

    private function registrarMovimentoEstoque(Produto $produto, string $tipo, float $quantidade, string $motivo, ?OsItem $item = null): bool
    {
        if ($quantidade <= 0 || !$this->produtoControlaEstoque($produto)) {
            return true;
        }

        try {
            app(EstoqueMovimentacaoService::class)->registrar(
                $produto,
                $tipo,
                $quantidade,
                $motivo,
                $item?->descricao,
                [
                    'origem' => 'ORDEM_SERVICO',
                    'origem_id' => $item?->ordem_servico_id ?? $this->os?->id,
                ]
            );
        } catch (\InvalidArgumentException $exception) {
            session()->flash('error', $exception->getMessage());
            return false;
        }

        return true;
    }

    private function precoProdutoParaOs(Produto $produto, ?string $tipoPreco = null): float
    {
        $precoPrazo = (float) ($produto->preco_venda_prazo ?: $produto->preco_venda_vista ?: 0);
        $precoVista = (float) ($produto->preco_venda_vista ?: $precoPrazo);

        if (($tipoPreco ?: 'prazo') === 'vista') {
            return $precoVista;
        }

        return $precoPrazo;
    }

    public function alterarQuantidade($itemId, $qtd)
    {
        $item = OsItem::find($itemId);
        if ($item && $qtd > 0) {
            if ($item->produto_id) {
                $produto = Produto::find($item->produto_id);
                if ($produto) {
                    $diferenca = (float)$qtd - $item->quantidade;
                    if ($diferenca > 0 && (float) $produto->estoque_atual < $diferenca) {
                        session()->flash('error', 'Estoque insuficiente para esta quantidade.');
                        return;
                    }
                    if ($diferenca > 0 && !$this->registrarMovimentoEstoque($produto, 'SAIDA', $diferenca, 'ALTERACAO DE QUANTIDADE NA OS #' . $item->ordem_servico_id, $item)) {
                        return;
                    }

                    if ($diferenca < 0 && !$this->registrarMovimentoEstoque($produto, 'ENTRADA', abs($diferenca), 'REDUCAO DE QUANTIDADE NA OS #' . $item->ordem_servico_id, $item)) {
                        return;
                    }
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
                    $prod->preco_venda_vista = $novoPreco;
                    $prod->save();
                }
            } elseif ($item->tipo === 'SERVICO') {
                $serv = Servico::where('descricao', $item->descricao)->first();
                if ($serv) {
                    $serv->valor_base = $novoPreco;
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
                $valor = $this->normalizarNumero($valor);
            }

            $item->$campo = $valor;

            if ($campo === 'quantidade') {
                $novaQtd = (float) $item->quantidade;
                if ($novaQtd <= 0) {
                    session()->flash('error', 'A quantidade deve ser maior que zero.');
                    return;
                }
            }

            if ($campo === 'desconto_valor') {
                $item->desconto_tipo = 'VALOR';
            }

            if ($campo === 'quantidade' && $item->produto_id) {
                $novaQtd = (float) $item->quantidade;
                $diferenca = $novaQtd - $item->getOriginal('quantidade');

                $produto = Produto::find($item->produto_id);
                if ($produto) {
                    if ($diferenca > 0 && (float) $produto->estoque_atual < $diferenca) {
                        session()->flash('error', 'Estoque insuficiente para esta quantidade.');
                        return;
                    }

                    if ($diferenca > 0) {
                        if (!$this->registrarMovimentoEstoque($produto, 'SAIDA', $diferenca, 'ALTERACAO DE QUANTIDADE NA OS #' . $item->ordem_servico_id, $item)) {
                            return;
                        }
                    } elseif ($diferenca < 0) {
                        if (!$this->registrarMovimentoEstoque($produto, 'ENTRADA', abs($diferenca), 'REDUCAO DE QUANTIDADE NA OS #' . $item->ordem_servico_id, $item)) {
                            return;
                        }
                    }
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
                    if (!$this->registrarMovimentoEstoque($produto, 'ENTRADA', (float) $item->quantidade, 'ITEM REMOVIDO DA OS #' . $item->ordem_servico_id, $item)) {
                        return;
                    }
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
        if (!$this->os) {
            $this->subtotal = 0;
            $this->desconto_reais = 0;
            $this->total_geral = 0;
            return;
        }

        $this->os->refresh();
        $itens = OsItem::where('ordem_servico_id', $this->os->id)->get();

        foreach ($itens as $item) {
            $quantidade = (float) $item->quantidade;
            $valorUnitario = (float) $item->valor_unitario;
            $totalBrutoItem = $quantidade * $valorUnitario;
            $descontoItem = (float) ($item->desconto_valor ?? 0);

            if (($item->desconto_tipo ?? 'VALOR') === 'PORCENTAGEM') {
                $descontoItem = $totalBrutoItem * ($descontoItem / 100);
            }

            $valorTotalItem = round(max(0, $totalBrutoItem - $descontoItem), 2);

            if ((float) $item->valor_total !== $valorTotalItem) {
                $item->valor_total = $valorTotalItem;
                $item->save();
            }
        }

        $itens = OsItem::where('ordem_servico_id', $this->os->id)->get();

        $subtotalBruto = (float) $itens->sum(fn ($item) => (float) $item->quantidade * (float) $item->valor_unitario);
        $totalPecas = (float) $itens->where('tipo', 'PECA')->sum('valor_total');
        $totalServicos = (float) $itens->where('tipo', 'SERVICO')->sum('valor_total');
        $subtotalComDescontoItens = $totalPecas + $totalServicos;
        $descontoItens = max(0, $subtotalBruto - $subtotalComDescontoItens);

        $subtotalGeral = $subtotalBruto;

        $descontoGeral = 0;
        $descontoGeralValorInput = (float) ($this->os->desconto_geral_valor ?? 0);

        if ($descontoGeralValorInput > 0) {
            if (($this->os->desconto_geral_tipo ?? 'VALOR') === 'PORCENTAGEM') {
                $descontoGeral = $subtotalComDescontoItens * ($descontoGeralValorInput / 100);
            } else {
                $descontoGeral = $descontoGeralValorInput;
            }
        }

        $descontoGeral = min($descontoGeral, $subtotalComDescontoItens);
        $valorLiquido = max(0, $subtotalComDescontoItens - $descontoGeral);

        $this->subtotal = $subtotalGeral;
        $this->desconto_reais = $descontoItens + $descontoGeral;
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

    public function alterarTipoPrecoItem($itemId, $tipoPreco)
    {
        $item = OsItem::find($itemId);
        if (!$item || !$item->produto_id || !in_array($tipoPreco, ['vista', 'prazo'])) {
            return;
        }

        $produto = Produto::find($item->produto_id);
        if (!$produto) {
            return;
        }

        $item->valor_unitario = $this->precoProdutoParaOs($produto, $tipoPreco);
        $this->recalcularItem($item);
        $item->save();

        $this->atualizarTotaisOs();
    }

    private function recalcularItem(OsItem $item): void
    {
        $totalBrutoItem = (float) $item->quantidade * (float) $item->valor_unitario;
        $descontoItem = 0;

        if ((float) ($item->desconto_valor ?? 0) > 0) {
            if (($item->desconto_tipo ?? 'VALOR') === 'PORCENTAGEM') {
                $descontoItem = $totalBrutoItem * ((float) $item->desconto_valor / 100);
            } else {
                $descontoItem = (float) $item->desconto_valor;
            }
        }

        $item->valor_total = round(max(0, $totalBrutoItem - $descontoItem), 2);
    }

    public function aplicarDescontoGeral()
    {
        $this->garantirOsCriada();

        $descontoPorcento = $this->normalizarNumero($this->desconto_porcento);
        $descontoReais = $this->normalizarNumero($this->desconto_reais);
        $itens = OsItem::where('ordem_servico_id', $this->os->id)->get();
        $itensParaDesconto = $this->itensParaAplicarDesconto($itens);

        if ($itens->isEmpty() || $itensParaDesconto->isEmpty()) {
            $this->os->update([
                'desconto_geral_tipo' => null,
                'desconto_geral_valor' => 0,
            ]);
            $this->atualizarTotaisOs();
            return;
        }

        if ($descontoPorcento > 0) {
            foreach ($itensParaDesconto as $item) {
                $item->desconto_tipo = 'PORCENTAGEM';
                $item->desconto_valor = min(100, $descontoPorcento);
                $item->save();
            }
        } elseif ($descontoReais > 0) {
            $subtotalSelecionado = $itensParaDesconto->sum(fn ($item) => (float) $item->quantidade * (float) $item->valor_unitario);
            $descontoRestante = min($descontoReais, $subtotalSelecionado);
            $ultimoItemId = $itensParaDesconto->last()?->id;

            foreach ($itensParaDesconto as $item) {
                $brutoItem = (float) $item->quantidade * (float) $item->valor_unitario;
                $descontoItem = $item->id === $ultimoItemId
                    ? $descontoRestante
                    : round($subtotalSelecionado > 0 ? ($brutoItem / $subtotalSelecionado) * min($descontoReais, $subtotalSelecionado) : 0, 2);

                $descontoItem = min($descontoItem, $brutoItem);
                $descontoRestante = max(0, $descontoRestante - $descontoItem);

                $item->desconto_tipo = 'VALOR';
                $item->desconto_valor = $descontoItem;
                $item->save();
            }
        } else {
            foreach ($this->itensParaAplicarDesconto($itens) as $item) {
                $item->desconto_tipo = null;
                $item->desconto_valor = 0;
                $item->save();
            }
        }

        $this->os->update([
            'desconto_geral_tipo' => null,
            'desconto_geral_valor' => 0,
        ]);

        $this->atualizarTotaisOs();
    }

    private function itensParaAplicarDesconto($itens)
    {
        if ($this->desconto_alvo === 'item') {
            if (!$this->itemSelecionadoId) {
                session()->flash('error', 'Selecione um item para aplicar desconto somente nele.');
                return collect();
            }

            $itemSelecionado = $itens->where('id', $this->itemSelecionadoId);

            if ($itemSelecionado->isNotEmpty()) {
                return $itemSelecionado->values();
            }

            return collect();
        }

        return $itens->values();
    }

    private function normalizarNumero($valor): float
    {
        $valor = trim((string) $valor);
        $valor = preg_replace('/[^\d,.\-]/', '', $valor);

        if ($valor === '' || $valor === '-' || $valor === null) {
            return 0;
        }

        if (str_contains($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        return max(0, (float) $valor);
    }

    public function salvarCampo($campo)
    {
        $this->garantirOsCriada();

        $camposTexto = ['placa_veiculo', 'marca_modelo_veiculo', 'km_veiculo', 'sintoma_reclamacao', 'nome_cartao'];
        if (in_array($campo, $camposTexto)) {
            $this->$campo = mb_strtoupper(trim((string) $this->$campo), 'UTF-8');
        }

        if ($campo === 'atendente_id') {
            $this->atendente_id = $this->validarFuncionarioId($this->atendente_id);
        }

        $osBanco = OrdemServico::find($this->os->id);
        if ($osBanco) {
            $osBanco->{$campo} = $this->$campo;
            $osBanco->save();
        }
    }

    public function abrirFinalizacao()
    {
        $this->garantirOsCriada();
        $this->atualizarTotaisOs();

        if (OsItem::where('ordem_servico_id', $this->os->id)->count() === 0) {
            session()->flash('error', 'Adicione pelo menos um produto ou servico antes de finalizar.');
            return;
        }

        $this->forma_pagamento = $this->forma_pagamento ?: ($this->os->forma_pagamento ?: 'PRAZO');
        $this->status_pagamento = $this->status_pagamento ?: ($this->os->status_pagamento ?: $this->statusPagamentoPadrao($this->forma_pagamento));
        $this->data_vencimento = $this->data_vencimento ?: ($this->os->data_vencimento ?? now()->addDays(30)->toDateString());
        $this->observacao_fechamento = $this->observacao_fechamento ?: ($this->os->observacao_fechamento ?? '');
        $this->etapaFinalizacao = 1;
        $this->modalFinalizacaoAberto = true;
    }

    public function avancarFinalizacao()
    {
        if ($this->etapaFinalizacao === 1) {
            $this->validarDadosFechamento();
        }

        $this->etapaFinalizacao = min(3, $this->etapaFinalizacao + 1);
    }

    public function voltarFinalizacao()
    {
        $this->etapaFinalizacao = max(1, $this->etapaFinalizacao - 1);
    }

    public function updatedFormaPagamento($value)
    {
        $this->status_pagamento = $this->statusPagamentoPadrao($value);

        if (in_array($value, ['PRAZO', 'BOLETO'])) {
            $this->data_vencimento = $this->data_vencimento ?: now()->addDays(30)->toDateString();
        } else {
            $this->data_vencimento = now()->toDateString();
        }
    }

    public function finalizarOs($abrirComprovante = true)
    {
        $this->garantirOsCriada();
        $this->validarDadosFechamento();
        $this->atualizarTotaisOs();

        if (OsItem::where('ordem_servico_id', $this->os->id)->count() === 0) {
            session()->flash('error', 'Adicione pelo menos um produto ou servico antes de finalizar.');
            return;
        }

        $this->os->update([
            'status' => 'FINALIZADO',
            'forma_pagamento' => $this->forma_pagamento,
            'status_pagamento' => $this->status_pagamento,
            'data_vencimento' => $this->data_vencimento ?: now()->addDays(30)->toDateString(),
            'finalizado_em' => now(),
            'cupom_fiscal_emitido' => false,
            'comprovante_emitido_em' => $abrirComprovante ? now() : null,
            'observacao_fechamento' => $this->observacao_fechamento ?: null,
        ]);

        $id = $this->os->id;
        AuditLog::registrar('ordem_servico', 'finalizada', 'OS finalizada.', $this->os, [
            'forma_pagamento' => $this->forma_pagamento,
            'status_pagamento' => $this->status_pagamento,
            'total' => $this->os->valor_total_liquido,
            'abrir_comprovante' => (bool) $abrirComprovante,
        ]);
        $this->modalFinalizacaoAberto = false;
        $this->os->refresh();

        if ($abrirComprovante) {
            return redirect()->route('os.comprovante', ['ordemServico' => $id, 'tipo' => 'comprovante']);
        }

        session()->flash('success', 'OS finalizada com sucesso.');

        $proximaOs = OrdemServico::where('status', 'RASCUNHO')
            ->where('id', '!=', $id)
            ->latest()
            ->first();

        return $proximaOs
            ? redirect()->route('os.editar', $proximaOs->id)
            : redirect()->route('os.nova');
    }

    private function validarDadosFechamento(): void
    {
        $this->validate([
            'forma_pagamento' => 'required|in:DINHEIRO,PIX,CARTAO_DEBITO,CARTAO_CREDITO,BOLETO,PRAZO',
            'status_pagamento' => 'required|in:PENDENTE,PARCIAL,PAGO',
            'data_vencimento' => 'required|date',
        ], [
            'forma_pagamento.required' => 'Informe a forma de pagamento.',
            'data_vencimento.required' => 'Informe a data de vencimento.',
        ]);
    }

    private function statusPagamentoPadrao($formaPagamento): string
    {
        return in_array($formaPagamento, ['DINHEIRO', 'PIX', 'CARTAO_DEBITO', 'CARTAO_CREDITO']) ? 'PAGO' : 'PENDENTE';
    }

    public function abrirCartoesFechados()
    {
        $this->modalCartoesFechadosAberto = true;
        $this->modalDevolucaoItemAberto = false;
        $this->modalCancelamentoVendaAberto = false;

        if (!$this->cartaoFechadoSelecionadoId) {
            $this->cartaoFechadoSelecionadoId = OrdemServico::whereIn('status', ['FINALIZADO', 'CANCELADO'])
                ->latest('updated_at')
                ->value('id');
        }
    }

    public function selecionarCartaoFechado($id)
    {
        $this->cartaoFechadoSelecionadoId = $id;
        $this->modalDevolucaoItemAberto = false;
        $this->modalCancelamentoVendaAberto = false;
    }

    public function limparFiltrosFechados()
    {
        $this->fechados_busca = '';
        $this->fechados_empresa = '';
        $this->fechados_cliente = '';
        $this->fechados_placa = '';
        $this->fechados_produto = '';
        $this->fechados_data_inicio = '';
        $this->fechados_data_fim = '';
        $this->fechados_status = 'FINALIZADO';
        $this->fechados_pagamento = '';
    }

    public function reabrirCartaoFechado($id)
    {
        $ordem = OrdemServico::findOrFail($id);

        if ($ordem->status === 'CANCELADO') {
            session()->flash('error', 'Venda cancelada nao pode voltar para edicao.');
            return;
        }

        $ordem->update([
            'status' => 'RASCUNHO',
            'finalizado_em' => null,
            'comprovante_emitido_em' => null,
        ]);

        AuditLog::registrar('ordem_servico', 'reaberta', 'OS finalizada voltou para edicao.', $ordem);

        return redirect()->route('os.editar', $ordem->id);
    }

    public function abrirDevolucaoItem($itemId)
    {
        $item = OsItem::with('ordemServico')->findOrFail($itemId);

        if (!$item->produto_id || !$item->ordemServico || $item->ordemServico->status !== 'FINALIZADO') {
            return;
        }

        $disponivel = max(0, (float) $item->quantidade - (float) ($item->quantidade_devolvida ?? 0));
        if ($disponivel <= 0) {
            session()->flash('info', 'Este item ja foi totalmente devolvido.');
            return;
        }

        $this->devolucao_item_id = $item->id;
        $this->devolucao_quantidade = number_format($disponivel, 3, ',', '');
        $this->devolucao_motivo = '';
        $this->modalDevolucaoItemAberto = true;
    }

    public function registrarDevolucaoItem()
    {
        $item = OsItem::with(['produto', 'ordemServico'])->findOrFail($this->devolucao_item_id);
        $quantidade = $this->normalizarNumero($this->devolucao_quantidade);
        $disponivel = max(0, (float) $item->quantidade - (float) ($item->quantidade_devolvida ?? 0));

        if (!$item->produto_id || !$item->produto || !$item->ordemServico || $item->ordemServico->status !== 'FINALIZADO') {
            return;
        }

        if ($quantidade <= 0 || $quantidade > $disponivel) {
            $this->addError('devolucao_quantidade', 'Informe uma quantidade valida para devolucao.');
            return;
        }

        if (!$this->registrarMovimentoEstoque($item->produto, 'ENTRADA', $quantidade, 'DEVOLUCAO DE ITEM DA OS #' . $item->ordem_servico_id, $item)) {
            return;
        }
        $item->quantidade_devolvida = (float) ($item->quantidade_devolvida ?? 0) + $quantidade;
        $item->devolvido_em = now();
        $item->motivo_devolucao = mb_strtoupper(trim((string) $this->devolucao_motivo), 'UTF-8') ?: 'DEVOLUCAO REGISTRADA';
        $item->save();

        AuditLog::registrar('ordem_servico', 'devolucao_item', 'Devolucao de item registrada com estorno de estoque.', $item->ordemServico, [
            'item_id' => $item->id,
            'produto_id' => $item->produto_id,
            'descricao' => $item->descricao,
            'quantidade' => $quantidade,
            'motivo' => $item->motivo_devolucao,
        ]);

        $this->modalDevolucaoItemAberto = false;
        $this->devolucao_item_id = null;
        $this->devolucao_quantidade = '';
        $this->devolucao_motivo = '';
        session()->flash('success', 'Devolucao registrada e estoque atualizado.');
    }

    public function abrirCancelamentoVenda($id)
    {
        $ordem = OrdemServico::findOrFail($id);

        if ($ordem->status === 'CANCELADO') {
            session()->flash('info', 'Esta venda ja esta cancelada.');
            return;
        }

        $this->cartaoFechadoSelecionadoId = $ordem->id;
        $this->motivo_cancelamento = '';
        $this->modalCancelamentoVendaAberto = true;
    }

    public function cancelarVendaFechada()
    {
        $ordem = OrdemServico::with('itens.produto')->findOrFail($this->cartaoFechadoSelecionadoId);

        if ($ordem->status === 'CANCELADO') {
            $this->modalCancelamentoVendaAberto = false;
            return;
        }

        $motivo = mb_strtoupper(trim((string) $this->motivo_cancelamento), 'UTF-8');
        if (strlen($motivo) < 5) {
            $this->addError('motivo_cancelamento', 'Informe o motivo do cancelamento.');
            return;
        }

        $this->devolverEstoqueDaOs($ordem, true, 'CANCELAMENTO: ' . $motivo);

        $ordem->update([
            'status' => 'CANCELADO',
            'cancelado_em' => now(),
            'motivo_cancelamento' => $motivo,
            'status_pagamento' => 'PENDENTE',
        ]);

        AuditLog::registrar('ordem_servico', 'cancelada', 'Venda cancelada com estorno de estoque.', $ordem, [
            'motivo' => $motivo,
            'total' => $ordem->valor_total_liquido,
        ]);

        $this->modalCancelamentoVendaAberto = false;
        session()->flash('success', 'Venda cancelada e estoque estornado.');
    }

    private function validarFuncionarioId($id): ?int
    {
        if (!$id) {
            return null;
        }

        return Funcionario::whereKey($id)->exists() ? (int) $id : null;
    }

    public function alternarCartao($id)
    {
        return $id ? redirect()->route('os.editar', $id) : redirect()->route('os.nova');
    }

    public function novoAtendimento($nome = '') {
        $novaOs = OrdemServico::create(['status' => 'RASCUNHO', 'nome_cartao' => mb_strtoupper(trim((string) $nome), 'UTF-8'), 'cliente_id' => null]);
        AuditLog::registrar('ordem_servico', 'criada', 'Novo atendimento criado.', $novaOs, [
            'nome_cartao' => $novaOs->nome_cartao,
        ]);
        return redirect()->route('os.editar', $novaOs->id);
    }

    public function excluirCartao() {
        if (!$this->os) {
            return redirect()->route('os.nova');
        }

        $idDeletado = $this->os->id;
        AuditLog::registrar('ordem_servico', 'excluida', 'Cartao de OS em rascunho excluido.', $this->os, [
            'nome_cartao' => $this->os->nome_cartao,
            'status' => $this->os->status,
        ]);
        $this->devolverEstoqueDaOs($this->os);
        $this->os->delete();
        $proximaOs = OrdemServico::where('id', '!=', $idDeletado)->where('status', 'RASCUNHO')->latest()->first();
        return $proximaOs
            ? redirect()->route('os.editar', $proximaOs->id)
            : redirect()->route('os.nova');
    }

    private function devolverEstoqueDaOs(OrdemServico $os, bool $apenasNaoDevolvido = false, ?string $motivo = null): void
    {
        $itens = OsItem::where('ordem_servico_id', $os->id)
            ->whereNotNull('produto_id')
            ->get();

        foreach ($itens as $item) {
            $produto = Produto::find($item->produto_id);
            if ($produto) {
                $quantidade = (float) $item->quantidade;

                if ($apenasNaoDevolvido) {
                    $quantidade = max(0, $quantidade - (float) ($item->quantidade_devolvida ?? 0));
                }

                if ($quantidade <= 0) {
                    continue;
                }

                if (!$this->registrarMovimentoEstoque($produto, 'ENTRADA', $quantidade, $motivo ?: 'ESTORNO DE ESTOQUE DA OS #' . $os->id, $item)) {
                    continue;
                }

                if ($apenasNaoDevolvido) {
                    $item->quantidade_devolvida = (float) ($item->quantidade_devolvida ?? 0) + $quantidade;
                    $item->devolvido_em = now();
                    $item->motivo_devolucao = $motivo ?: 'ESTORNO DE ESTOQUE';
                    $item->save();
                }
            }
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $listaCartoes = OrdemServico::where('status', 'RASCUNHO')->orderBy('id', 'desc')->get();

        // SINCRONISMO SÊNIOR: Alimenta a propriedade pública antes da renderização para blindar a Blade contra erros
        $this->itensDaOs = $this->os
            ? OsItem::with('produto')->where('ordem_servico_id', $this->os->id)->get()
            : collect();

        $listaClientesModal = collect();
        $totaisAbertosClientes = collect();
        $clienteHistorico = null;
        $historicoOsCliente = collect();
        $totalAbertoHistorico = 0;

        if ($this->modalClienteAberto) {
            $clientesQuery = Cliente::query()
                ->withCount('ordensServico')
                ->when($this->pesquisaCliente, function ($query) {
                    $termo = '%' . $this->pesquisaCliente . '%';

                    $query->where(function ($subQuery) use ($termo) {
                        $subQuery->where('nome', 'like', $termo)
                            ->orWhere('cpf_cnpj', 'like', $termo)
                            ->orWhere('whatsapp', 'like', $termo)
                            ->orWhere('email', 'like', $termo)
                            ->orWhere('cidade', 'like', $termo);
                    });
                })
                ->when($this->filtroClienteSituacao === 'com_os', function ($query) {
                    $query->whereHas('ordensServico');
                })
                ->when($this->filtroClienteSituacao === 'com_debito', function ($query) {
                    $query->whereHas('ordensServico', function ($subQuery) {
                        $subQuery->where('status_pagamento', '!=', 'PAGO')
                            ->where('valor_total_liquido', '>', 0);
                    });
                })
                ->orderBy('nome');

            $listaClientesModal = $clientesQuery->take(15)->get();

            $totaisAbertosClientes = OrdemServico::whereIn('cliente_id', $listaClientesModal->pluck('id'))
                ->where('status_pagamento', '!=', 'PAGO')
                ->selectRaw('cliente_id, SUM(valor_total_liquido) as total_aberto')
                ->groupBy('cliente_id')
                ->pluck('total_aberto', 'cliente_id');

            if ($this->clienteHistoricoId) {
                $clienteHistorico = Cliente::withCount('ordensServico')->find($this->clienteHistoricoId);
                $historicoOsCliente = OrdemServico::where('cliente_id', $this->clienteHistoricoId)
                    ->latest()
                    ->take(8)
                    ->get();
                $totalAbertoHistorico = (float) OrdemServico::where('cliente_id', $this->clienteHistoricoId)
                    ->where('status_pagamento', '!=', 'PAGO')
                    ->sum('valor_total_liquido');
            }
        }

        $produtosModal = collect();
        $servicosModal = collect();

        if ($this->modalProdutoAberto) {
            $pesquisaCatalogo = trim((string) $this->pesquisaProduto);
            $termoCatalogo = '%' . $pesquisaCatalogo . '%';
            $codigoCatalogo = preg_replace('/[^0-9]/', '', $pesquisaCatalogo);

            $produtosModal = Produto::query()
                ->where(function ($query) {
                    $query->where('ativo', true)
                        ->orWhereNull('ativo');
                })
                ->when($pesquisaCatalogo !== '', function ($query) use ($termoCatalogo, $codigoCatalogo) {
                    $query->where(function ($subQuery) use ($termoCatalogo, $codigoCatalogo) {
                        $subQuery->where('nome', 'like', $termoCatalogo)
                            ->orWhere('descricao_detalhada', 'like', $termoCatalogo)
                            ->orWhere('marca', 'like', $termoCatalogo)
                            ->orWhere('categoria', 'like', $termoCatalogo)
                            ->orWhere('codigo_interno', 'like', $termoCatalogo)
                            ->orWhere('codigo_barras', 'like', $termoCatalogo);

                        if ($codigoCatalogo !== '') {
                            $subQuery->orWhere('codigo_interno', 'like', '%' . $codigoCatalogo . '%')
                                ->orWhere('codigo_barras', 'like', '%' . $codigoCatalogo . '%');
                        }
                    });
                })
                ->orderByRaw("CASE WHEN tipo = 'S' THEN 1 ELSE 0 END")
                ->orderBy('nome')
                ->get();

            $servicosModal = Servico::query()
                ->when($pesquisaCatalogo !== '', function ($query) use ($termoCatalogo) {
                    $query->where(function ($subQuery) use ($termoCatalogo) {
                        $subQuery->where('nome', 'like', $termoCatalogo)
                            ->orWhere('descricao', 'like', $termoCatalogo);
                    });
                })
                ->orderBy('nome')
                ->get();

        }

        $empresa = Empresa::first();
        $cartoesFechados = collect();
        $cartaoFechadoSelecionado = null;
        $resumoFechados = [
            'quantidade' => 0,
            'total' => 0,
            'pendente' => 0,
            'cancelados' => 0,
        ];

        if ($this->modalCartoesFechadosAberto) {
            $cartoesFechadosQuery = OrdemServico::query()
                ->with(['cliente', 'atendente', 'itens.produto'])
                ->whereIn('status', ['FINALIZADO', 'CANCELADO'])
                ->when($this->fechados_status && $this->fechados_status !== 'TODOS', function ($query) {
                    $query->where('status', $this->fechados_status);
                })
                ->when($this->fechados_pagamento, function ($query) {
                    $query->where('forma_pagamento', $this->fechados_pagamento);
                })
                ->when($this->fechados_cliente, function ($query) {
                    $termo = '%' . $this->fechados_cliente . '%';
                    $query->whereHas('cliente', function ($subQuery) use ($termo) {
                        $subQuery->where('nome', 'like', $termo)
                            ->orWhere('cpf_cnpj', 'like', $termo)
                            ->orWhere('whatsapp', 'like', $termo);
                    });
                })
                ->when($this->fechados_placa, function ($query) {
                    $query->where('placa_veiculo', 'like', '%' . $this->fechados_placa . '%');
                })
                ->when($this->fechados_produto, function ($query) {
                    $termo = '%' . $this->fechados_produto . '%';
                    $query->whereHas('itens', function ($subQuery) use ($termo) {
                        $subQuery->where('descricao', 'like', $termo)
                            ->orWhereHas('produto', function ($produtoQuery) use ($termo) {
                                $produtoQuery->where('nome', 'like', $termo)
                                    ->orWhere('codigo_interno', 'like', $termo)
                                    ->orWhere('codigo_barras', 'like', $termo)
                                    ->orWhere('marca', 'like', $termo);
                            });
                    });
                })
                ->when($this->fechados_data_inicio, function ($query) {
                    $query->where(function ($subQuery) {
                        $subQuery->whereDate('finalizado_em', '>=', $this->fechados_data_inicio)
                            ->orWhereDate('updated_at', '>=', $this->fechados_data_inicio);
                    });
                })
                ->when($this->fechados_data_fim, function ($query) {
                    $query->where(function ($subQuery) {
                        $subQuery->whereDate('finalizado_em', '<=', $this->fechados_data_fim)
                            ->orWhereDate('updated_at', '<=', $this->fechados_data_fim);
                    });
                })
                ->when($this->fechados_busca, function ($query) {
                    $termo = '%' . $this->fechados_busca . '%';
                    $query->where(function ($subQuery) use ($termo) {
                        $subQuery->where('id', 'like', $termo)
                            ->orWhere('placa_veiculo', 'like', $termo)
                            ->orWhere('marca_modelo_veiculo', 'like', $termo)
                            ->orWhereHas('cliente', function ($clienteQuery) use ($termo) {
                                $clienteQuery->where('nome', 'like', $termo)
                                    ->orWhere('cpf_cnpj', 'like', $termo);
                            })
                            ->orWhereHas('itens', function ($itemQuery) use ($termo) {
                                $itemQuery->where('descricao', 'like', $termo);
                            });
                    });
                });

            if ($this->fechados_empresa && $empresa) {
                $empresaTexto = mb_strtoupper(($empresa->nome_fantasia ?? '') . ' ' . ($empresa->razao_social ?? '') . ' ' . ($empresa->cnpj ?? ''), 'UTF-8');
                if (!str_contains($empresaTexto, mb_strtoupper($this->fechados_empresa, 'UTF-8'))) {
                    $cartoesFechadosQuery->whereRaw('1 = 0');
                }
            }

            $todosParaResumo = (clone $cartoesFechadosQuery)->get();
            $resumoFechados = [
                'quantidade' => $todosParaResumo->count(),
                'total' => (float) $todosParaResumo->where('status', 'FINALIZADO')->sum('valor_total_liquido'),
                'pendente' => (float) $todosParaResumo->where('status_pagamento', '!=', 'PAGO')->where('status', 'FINALIZADO')->sum('valor_total_liquido'),
                'cancelados' => $todosParaResumo->where('status', 'CANCELADO')->count(),
            ];

            $cartoesFechados = $cartoesFechadosQuery
                ->latest('updated_at')
                ->take(30)
                ->get();

            if (!$this->cartaoFechadoSelecionadoId || !$cartoesFechados->contains('id', $this->cartaoFechadoSelecionadoId)) {
                $this->cartaoFechadoSelecionadoId = $cartoesFechados->first()?->id;
            }

            if ($this->cartaoFechadoSelecionadoId) {
                $cartaoFechadoSelecionado = OrdemServico::with(['cliente', 'atendente', 'itens.produto'])
                    ->find($this->cartaoFechadoSelecionadoId);
            }
        }

        $atendentes = Funcionario::where('ativo', true)
            ->whereIn('cargo', ['ATENDENTE', 'GERENTE'])
            ->orderBy('nome')
            ->get();

        return view('livewire.ordem-servico.gerenciar-os', [
            'os' => $this->os,
            'nome_cartao' => $this->nome_cartao,
            'subtotal' => $this->subtotal,
            'desconto_reais' => $this->desconto_reais,
            'total_geral' => $this->total_geral,
            'itemSelecionadoId' => $this->itemSelecionadoId,
            'editandoItemId' => $this->editandoItemId,
            'clienteHistoricoId' => $this->clienteHistoricoId,
            'listaCartoes' => $listaCartoes,
            'itensDaOs' => $this->itensDaOs,
            'listaClientesModal' => $listaClientesModal,
            'totaisAbertosClientes' => $totaisAbertosClientes,
            'clienteHistorico' => $clienteHistorico,
            'historicoOsCliente' => $historicoOsCliente,
            'totalAbertoHistorico' => $totalAbertoHistorico,
            'produtosModal' => $produtosModal,
            'servicosModal' => $servicosModal,
            'atendentes' => $atendentes,
            'empresa' => $empresa,
            'cartoesFechados' => $cartoesFechados,
            'cartaoFechadoSelecionado' => $cartaoFechadoSelecionado,
            'resumoFechados' => $resumoFechados,
        ]);
    }
}
