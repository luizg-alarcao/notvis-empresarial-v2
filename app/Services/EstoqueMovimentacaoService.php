<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\EstoqueMovimentacao;
use App\Models\Produto;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EstoqueMovimentacaoService
{
    public function registrar(
        Produto|int $produto,
        string $tipo,
        float $quantidade,
        string $motivo,
        ?string $observacao = null,
        array $contexto = []
    ): EstoqueMovimentacao {
        $produto = $produto instanceof Produto ? $produto : Produto::findOrFail($produto);
        $tipo = mb_strtoupper(trim($tipo), 'UTF-8');
        $motivo = mb_strtoupper(trim($motivo), 'UTF-8');
        $quantidade = round($quantidade, 3);

        if (!in_array($tipo, ['ENTRADA', 'SAIDA', 'AJUSTE'], true)) {
            throw new InvalidArgumentException('Tipo de movimentação de estoque inválido.');
        }

        if ($tipo === 'AJUSTE') {
            if ($quantidade < 0) {
                throw new InvalidArgumentException('O estoque ajustado não pode ser negativo.');
            }
        } elseif ($quantidade <= 0) {
            throw new InvalidArgumentException('Informe uma quantidade maior que zero.');
        }

        if (strlen($motivo) < 3) {
            throw new InvalidArgumentException('Informe um motivo para a movimentação.');
        }

        $tipoProduto = mb_strtoupper((string) ($produto->tipo ?? 'P'), 'UTF-8');
        $produtoEhServico = in_array($tipoProduto, ['S', 'SERVICO', 'SERVIÇO'], true);

        if ($produtoEhServico || !(bool) ($produto->controla_estoque ?? true)) {
            throw new InvalidArgumentException('Este item não controla estoque.');
        }

        return DB::transaction(function () use ($produto, $tipo, $quantidade, $motivo, $observacao, $contexto) {
            $produtoBloqueado = Produto::whereKey($produto->id)->lockForUpdate()->firstOrFail();
            $estoqueAnterior = round((float) ($produtoBloqueado->estoque_atual ?? 0), 3);

            $estoquePosterior = match ($tipo) {
                'ENTRADA' => $estoqueAnterior + $quantidade,
                'SAIDA' => $estoqueAnterior - $quantidade,
                'AJUSTE' => $quantidade,
            };

            $estoquePosterior = round($estoquePosterior, 3);

            if ($estoquePosterior < 0) {
                throw new InvalidArgumentException('Estoque insuficiente para esta movimentacao.');
            }

            $produtoBloqueado->forceFill([
                'estoque_atual' => $estoquePosterior,
            ])->save();

            $movimentacao = EstoqueMovimentacao::create([
                'produto_id' => $produtoBloqueado->id,
                'user_id' => auth()->id(),
                'tipo' => $tipo,
                'quantidade' => $quantidade,
                'estoque_anterior' => $estoqueAnterior,
                'estoque_posterior' => $estoquePosterior,
                'motivo' => $motivo,
                'observacao' => $observacao ? mb_strtoupper(trim($observacao), 'UTF-8') : null,
                'origem' => $contexto['origem'] ?? null,
                'origem_id' => $contexto['origem_id'] ?? null,
            ]);

            AuditLog::registrar('estoque', mb_strtolower($tipo, 'UTF-8'), 'Movimentação de estoque registrada.', $movimentacao, [
                'produto_id' => $produtoBloqueado->id,
                'produto' => $produtoBloqueado->nome,
                'tipo' => $tipo,
                'quantidade' => $quantidade,
                'estoque_anterior' => $estoqueAnterior,
                'estoque_posterior' => $estoquePosterior,
                'motivo' => $motivo,
            ]);

            return $movimentacao;
        });
    }
}
