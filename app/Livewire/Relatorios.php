<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\OrdemServico;
use App\Models\OsItem;
use App\Models\Produto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Relatorios extends Component
{
    public string $aba = 'fechamentos';
    public string $data_inicio = '';
    public string $data_fim = '';
    public string $busca = '';
    public string $cliente = '';
    public string $placa = '';
    public string $produto = '';
    public string $status = 'TODOS';
    public string $forma_pagamento = '';

    public function mount(): void
    {
        $abas = ['fechamentos', 'faturamento', 'produtos', 'clientes', 'estoque'];
        $aba = request('aba');

        $this->aba = in_array($aba, $abas, true) ? $aba : 'fechamentos';
        $this->data_inicio = now()->startOfMonth()->toDateString();
        $this->data_fim = now()->toDateString();
    }

    public function setAba(string $aba): void
    {
        if (in_array($aba, ['fechamentos', 'faturamento', 'produtos', 'clientes', 'estoque'], true)) {
            $this->aba = $aba;
        }
    }

    public function limparFiltros(): void
    {
        $this->busca = '';
        $this->cliente = '';
        $this->placa = '';
        $this->produto = '';
        $this->status = 'TODOS';
        $this->forma_pagamento = '';
        $this->data_inicio = now()->startOfMonth()->toDateString();
        $this->data_fim = now()->toDateString();
    }

    private function queryFechamentos(): Builder
    {
        return OrdemServico::query()
            ->whereIn('status', ['FINALIZADO', 'CANCELADO'])
            ->when($this->status !== '' && $this->status !== 'TODOS', fn ($query) => $query->where('status', $this->status))
            ->when($this->forma_pagamento, fn ($query) => $query->where('forma_pagamento', $this->forma_pagamento))
            ->when($this->cliente, function ($query) {
                $termo = '%' . $this->cliente . '%';
                $query->whereHas('cliente', function ($clienteQuery) use ($termo) {
                    $clienteQuery->where('nome', 'like', $termo)
                        ->orWhere('cpf_cnpj', 'like', $termo)
                        ->orWhere('whatsapp', 'like', $termo);
                });
            })
            ->when($this->placa, fn ($query) => $query->where('placa_veiculo', 'like', '%' . $this->placa . '%'))
            ->when($this->produto, function ($query) {
                $termo = '%' . $this->produto . '%';
                $query->whereHas('itens', function ($itemQuery) use ($termo) {
                    $itemQuery->where('descricao', 'like', $termo)
                        ->orWhereHas('produto', function ($produtoQuery) use ($termo) {
                            $produtoQuery->where('nome', 'like', $termo)
                                ->orWhere('codigo_interno', 'like', $termo)
                                ->orWhere('codigo_barras', 'like', $termo)
                                ->orWhere('marca', 'like', $termo);
                        });
                });
            })
            ->when($this->busca, function ($query) {
                $termo = '%' . $this->busca . '%';
                $query->where(function ($subQuery) use ($termo) {
                    $subQuery->where('id', 'like', $termo)
                        ->orWhere('placa_veiculo', 'like', $termo)
                        ->orWhere('marca_modelo_veiculo', 'like', $termo)
                        ->orWhere('forma_pagamento', 'like', $termo)
                        ->orWhereHas('cliente', function ($clienteQuery) use ($termo) {
                            $clienteQuery->where('nome', 'like', $termo)
                                ->orWhere('cpf_cnpj', 'like', $termo);
                        })
                        ->orWhereHas('itens', fn ($itemQuery) => $itemQuery->where('descricao', 'like', $termo));
                });
            })
            ->when($this->data_inicio, function ($query) {
                $query->whereRaw('DATE(COALESCE(finalizado_em, updated_at)) >= ?', [$this->data_inicio]);
            })
            ->when($this->data_fim, function ($query) {
                $query->whereRaw('DATE(COALESCE(finalizado_em, updated_at)) <= ?', [$this->data_fim]);
            });
    }

    private function resumoFechamentos(Collection $ordens): array
    {
        $finalizadas = $ordens->where('status', 'FINALIZADO');
        $total = (float) $finalizadas->sum('valor_total_liquido');
        $quantidade = $ordens->count();

        return [
            'quantidade' => $quantidade,
            'finalizadas' => $finalizadas->count(),
            'canceladas' => $ordens->where('status', 'CANCELADO')->count(),
            'total' => $total,
            'pendente' => (float) $finalizadas->where('status_pagamento', '!=', 'PAGO')->sum('valor_total_liquido'),
            'ticket_medio' => $finalizadas->count() > 0 ? $total / $finalizadas->count() : 0,
        ];
    }

    private function relatorioProdutos()
    {
        return OsItem::query()
            ->join('ordens_servico', 'ordens_servico.id', '=', 'os_itens.ordem_servico_id')
            ->leftJoin('produtos', 'produtos.id', '=', 'os_itens.produto_id')
            ->where('ordens_servico.status', 'FINALIZADO')
            ->when($this->data_inicio, fn ($query) => $query->whereRaw('DATE(COALESCE(ordens_servico.finalizado_em, ordens_servico.updated_at)) >= ?', [$this->data_inicio]))
            ->when($this->data_fim, fn ($query) => $query->whereRaw('DATE(COALESCE(ordens_servico.finalizado_em, ordens_servico.updated_at)) <= ?', [$this->data_fim]))
            ->when($this->produto ?: $this->busca, function ($query) {
                $termo = '%' . ($this->produto ?: $this->busca) . '%';
                $query->where(function ($subQuery) use ($termo) {
                    $subQuery->where('os_itens.descricao', 'like', $termo)
                        ->orWhere('produtos.nome', 'like', $termo)
                        ->orWhere('produtos.codigo_interno', 'like', $termo)
                        ->orWhere('produtos.codigo_barras', 'like', $termo)
                        ->orWhere('produtos.marca', 'like', $termo);
                });
            })
            ->selectRaw('
                os_itens.tipo,
                os_itens.produto_id,
                COALESCE(produtos.nome, os_itens.descricao) as descricao,
                produtos.codigo_interno,
                produtos.codigo_barras,
                produtos.preco_custo,
                SUM(os_itens.quantidade) as quantidade,
                SUM(os_itens.valor_total) as total,
                AVG(os_itens.valor_unitario) as valor_medio,
                SUM((os_itens.quantidade * os_itens.valor_unitario) - os_itens.valor_total) as descontos
            ')
            ->groupBy('os_itens.tipo', 'os_itens.produto_id', 'produtos.nome', 'os_itens.descricao', 'produtos.codigo_interno', 'produtos.codigo_barras', 'produtos.preco_custo')
            ->orderByDesc('total')
            ->limit(30)
            ->get()
            ->map(function ($item) {
                $custo = (float) ($item->preco_custo ?? 0) * (float) $item->quantidade;
                $item->lucro_estimado = (float) $item->total - $custo;
                $item->margem_estimada = (float) $item->total > 0 ? ($item->lucro_estimado / (float) $item->total) * 100 : 0;
                return $item;
            });
    }

    private function relatorioClientes()
    {
        return OrdemServico::query()
            ->with('cliente')
            ->where('status', 'FINALIZADO')
            ->when($this->data_inicio, fn ($query) => $query->whereRaw('DATE(COALESCE(finalizado_em, updated_at)) >= ?', [$this->data_inicio]))
            ->when($this->data_fim, fn ($query) => $query->whereRaw('DATE(COALESCE(finalizado_em, updated_at)) <= ?', [$this->data_fim]))
            ->when($this->cliente ?: $this->busca, function ($query) {
                $termo = '%' . ($this->cliente ?: $this->busca) . '%';
                $query->whereHas('cliente', function ($clienteQuery) use ($termo) {
                    $clienteQuery->where('nome', 'like', $termo)
                        ->orWhere('cpf_cnpj', 'like', $termo)
                        ->orWhere('whatsapp', 'like', $termo)
                        ->orWhere('email', 'like', $termo);
                });
            })
            ->selectRaw('
                cliente_id,
                COUNT(*) as quantidade_os,
                SUM(valor_total_liquido) as total,
                SUM(CASE WHEN status_pagamento <> "PAGO" THEN valor_total_liquido ELSE 0 END) as pendente,
                MAX(COALESCE(finalizado_em, updated_at)) as ultima_os
            ')
            ->groupBy('cliente_id')
            ->orderByDesc('total')
            ->limit(30)
            ->get();
    }

    private function relatorioEstoque(): array
    {
        $produtosBase = Produto::query()
            ->where('tipo', 'P')
            ->where('ativo', true)
            ->where('controla_estoque', true);

        $estoqueBaixo = (clone $produtosBase)
            ->whereRaw('COALESCE(estoque_atual, 0) <= COALESCE(estoque_minimo, 0)')
            ->orderBy('estoque_atual')
            ->limit(25)
            ->get();

        $semEstoque = (clone $produtosBase)
            ->whereRaw('COALESCE(estoque_atual, 0) <= 0')
            ->count();

        $valorEstoque = (float) (clone $produtosBase)
            ->selectRaw('SUM(COALESCE(estoque_atual, 0) * COALESCE(preco_custo, 0)) as total')
            ->value('total');

        $maiorValor = (clone $produtosBase)
            ->selectRaw('produtos.*, (COALESCE(estoque_atual, 0) * COALESCE(preco_custo, 0)) as valor_estoque')
            ->orderByDesc('valor_estoque')
            ->limit(15)
            ->get();

        return [
            'baixo' => $estoqueBaixo,
            'sem_estoque' => $semEstoque,
            'valor_estoque' => $valorEstoque,
            'maior_valor' => $maiorValor,
            'produtos_controlados' => (clone $produtosBase)->count(),
        ];
    }

    public function render()
    {
        $fechamentosQuery = $this->queryFechamentos();
        $todosFechamentos = (clone $fechamentosQuery)->get();
        $fechamentos = (clone $fechamentosQuery)
            ->with(['cliente', 'atendente', 'itens.produto'])
            ->latest('updated_at')
            ->limit(50)
            ->get();

        $finalizadas = $todosFechamentos->where('status', 'FINALIZADO');
        $porPagamento = $finalizadas
            ->groupBy(fn ($ordem) => $ordem->forma_pagamento ?: 'NAO INFORMADO')
            ->map(fn ($grupo, $forma) => [
                'forma' => $forma,
                'quantidade' => $grupo->count(),
                'total' => (float) $grupo->sum('valor_total_liquido'),
            ])
            ->sortByDesc('total')
            ->values();

        $porDia = $finalizadas
            ->groupBy(function ($ordem) {
                $data = $ordem->finalizado_em ?: $ordem->updated_at;
                return $data ? \Carbon\Carbon::parse($data)->format('d/m/Y') : '-';
            })
            ->map(fn ($grupo, $data) => [
                'data' => $data,
                'quantidade' => $grupo->count(),
                'total' => (float) $grupo->sum('valor_total_liquido'),
            ])
            ->sortByDesc('data')
            ->values();

        return view('livewire.relatorios', [
            'fechamentos' => $fechamentos,
            'resumoFechamentos' => $this->resumoFechamentos($todosFechamentos),
            'porPagamento' => $porPagamento,
            'porDia' => $porDia,
            'produtosRelatorio' => $this->relatorioProdutos(),
            'clientesRelatorio' => $this->relatorioClientes(),
            'estoqueRelatorio' => $this->relatorioEstoque(),
        ])->layout('layouts.app');
    }
}
