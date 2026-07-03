<?php

namespace App\Livewire\Estoque;

use App\Models\EstoqueMovimentacao;
use App\Models\Produto;
use App\Services\EstoqueMovimentacaoService;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Movimentacoes extends Component
{
    public string $produto_id = '';
    public string $tipo = 'ENTRADA';
    public string $quantidade = '';
    public string $motivo = '';
    public string $observacao = '';

    public string $busca = '';
    public string $filtro_tipo = '';
    public string $filtro_produto = '';
    public string $data_inicio = '';
    public string $data_fim = '';

    public function mount(): void
    {
        $this->data_inicio = now()->startOfMonth()->toDateString();
        $this->data_fim = now()->toDateString();
    }

    public function selecionarTipo(string $tipo): void
    {
        if (in_array($tipo, ['ENTRADA', 'SAIDA', 'AJUSTE'], true)) {
            $this->tipo = $tipo;
            $this->resetErrorBag('quantidade');
        }
    }

    public function limparFormulario(): void
    {
        $this->produto_id = '';
        $this->tipo = 'ENTRADA';
        $this->quantidade = '';
        $this->motivo = '';
        $this->observacao = '';
        $this->resetErrorBag();
    }

    public function limparFiltros(): void
    {
        $this->busca = '';
        $this->filtro_tipo = '';
        $this->filtro_produto = '';
        $this->data_inicio = now()->startOfMonth()->toDateString();
        $this->data_fim = now()->toDateString();
    }

    public function registrarMovimentacao(EstoqueMovimentacaoService $estoqueService): void
    {
        $quantidade = $this->normalizarNumero($this->quantidade);

        $dados = [
            'produto_id' => $this->produto_id,
            'tipo' => $this->tipo,
            'quantidade' => $quantidade,
            'motivo' => trim($this->motivo),
            'observacao' => trim($this->observacao),
        ];

        Validator::make($dados, [
            'produto_id' => ['required', 'exists:produtos,id'],
            'tipo' => ['required', 'in:ENTRADA,SAIDA,AJUSTE'],
            'quantidade' => ['required', 'numeric', $this->tipo === 'AJUSTE' ? 'min:0' : 'min:0.001'],
            'motivo' => ['required', 'min:3', 'max:120'],
            'observacao' => ['nullable', 'max:500'],
        ], [
            'produto_id.required' => 'Selecione o produto.',
            'produto_id.exists' => 'Produto não encontrado.',
            'quantidade.required' => 'Informe a quantidade.',
            'quantidade.numeric' => 'Informe uma quantidade válida.',
            'quantidade.min' => $this->tipo === 'AJUSTE'
                ? 'O estoque final não pode ser negativo.'
                : 'Informe uma quantidade maior que zero.',
            'motivo.required' => 'Informe o motivo.',
            'motivo.min' => 'Informe um motivo mais claro.',
            'motivo.max' => 'O motivo pode ter no máximo 120 caracteres.',
            'observacao.max' => 'A observação pode ter no máximo 500 caracteres.',
        ])->validate();

        try {
            $estoqueService->registrar(
                (int) $this->produto_id,
                $this->tipo,
                $quantidade,
                $dados['motivo'],
                $dados['observacao'] ?: null,
                ['origem' => 'MOVIMENTACAO_MANUAL']
            );
        } catch (\InvalidArgumentException $exception) {
            $this->addError('quantidade', $exception->getMessage());
            return;
        }

        $this->quantidade = '';
        $this->motivo = '';
        $this->observacao = '';

        session()->flash('success', 'Movimentação registrada e estoque atualizado.');
    }

    private function normalizarNumero($valor): float
    {
        $valor = trim((string) $valor);
        $valor = preg_replace('/[^0-9,.\-]/', '', $valor) ?: '0';

        if (str_contains($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        return (float) $valor;
    }

    private function produtosBase()
    {
        return Produto::query()
            ->where('ativo', true)
            ->where('controla_estoque', true)
            ->where(function ($query) {
                $query->where('tipo', 'P')->orWhereNull('tipo');
            });
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $produtos = $this->produtosBase()
            ->orderBy('nome')
            ->get();

        $produtoSelecionado = $this->produto_id
            ? Produto::find($this->produto_id)
            : null;

        $quantidadePreview = $this->quantidade !== ''
            ? $this->normalizarNumero($this->quantidade)
            : null;

        $previsaoEstoque = null;
        if ($produtoSelecionado && $quantidadePreview !== null) {
            $estoqueAtual = (float) ($produtoSelecionado->estoque_atual ?? 0);
            $previsaoEstoque = match ($this->tipo) {
                'ENTRADA' => $estoqueAtual + $quantidadePreview,
                'SAIDA' => $estoqueAtual - $quantidadePreview,
                'AJUSTE' => $quantidadePreview,
                default => $estoqueAtual,
            };
        }

        $movimentosBase = EstoqueMovimentacao::query()
            ->with(['produto', 'usuario'])
            ->when($this->filtro_tipo, fn ($query) => $query->where('tipo', $this->filtro_tipo))
            ->when($this->filtro_produto, fn ($query) => $query->where('produto_id', $this->filtro_produto))
            ->when($this->data_inicio, fn ($query) => $query->whereDate('created_at', '>=', $this->data_inicio))
            ->when($this->data_fim, fn ($query) => $query->whereDate('created_at', '<=', $this->data_fim))
            ->when($this->busca, function ($query) {
                $termo = '%' . $this->busca . '%';
                $query->where(function ($subQuery) use ($termo) {
                    $subQuery->where('motivo', 'like', $termo)
                        ->orWhere('observacao', 'like', $termo)
                        ->orWhereHas('produto', function ($produtoQuery) use ($termo) {
                            $produtoQuery->where('nome', 'like', $termo)
                                ->orWhere('codigo_interno', 'like', $termo)
                                ->orWhere('codigo_barras', 'like', $termo)
                                ->orWhere('marca', 'like', $termo);
                        });
                });
            });

        $resumo = [
            'entradas' => (float) (clone $movimentosBase)->where('tipo', 'ENTRADA')->sum('quantidade'),
            'saidas' => (float) (clone $movimentosBase)->where('tipo', 'SAIDA')->sum('quantidade'),
            'ajustes' => (clone $movimentosBase)->where('tipo', 'AJUSTE')->count(),
            'total' => (clone $movimentosBase)->count(),
        ];

        $movimentacoes = (clone $movimentosBase)
            ->latest()
            ->limit(100)
            ->get();

        $produtosBaixo = $this->produtosBase()
            ->whereRaw('COALESCE(estoque_atual, 0) <= COALESCE(estoque_minimo, 0)')
            ->orderBy('estoque_atual')
            ->limit(8)
            ->get();

        return view('livewire.estoque.movimentacoes', [
            'produtos' => $produtos,
            'produtoSelecionado' => $produtoSelecionado,
            'previsaoEstoque' => $previsaoEstoque,
            'movimentacoes' => $movimentacoes,
            'produtosBaixo' => $produtosBaixo,
            'resumo' => $resumo,
        ]);
    }
}
