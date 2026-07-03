<?php

namespace Tests\Feature;

use App\Livewire\OrdemServico\GerenciarOs;
use App\Models\OrdemServico;
use App\Models\OsItem;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrdemServicoFluxoTest extends TestCase
{
    use RefreshDatabase;

    private function produto(array $dados = []): Produto
    {
        return Produto::create(array_merge([
            'nome' => 'RELE AUX 40A',
            'tipo' => 'P',
            'unidade' => 'UN',
            'categoria' => 'ELETRICA',
            'preco_custo' => 30,
            'margem_lucro' => 50,
            'preco_venda_vista' => 42.75,
            'preco_venda_prazo' => 45,
            'estoque_atual' => 10,
            'estoque_minimo' => 2,
            'controla_estoque' => true,
            'ativo' => true,
        ], $dados));
    }

    public function test_os_item_quantity_updates_subtotal_total_and_stock(): void
    {
        $user = User::factory()->create(['perfil' => 'ADMIN']);
        $produto = $this->produto(['estoque_atual' => 10, 'preco_venda_prazo' => 45]);

        $component = Livewire::actingAs($user)
            ->test(GerenciarOs::class)
            ->call('adicionarItemDireto', $produto->id, 'produto', $produto->nome, null, 'prazo');

        $item = OsItem::firstOrFail();

        $component->call('atualizarCampo', $item->id, 'quantidade', 2);

        $item->refresh();
        $produto->refresh();

        $this->assertEquals(2.0, (float) $item->quantidade);
        $this->assertEquals(90.0, (float) $item->valor_total);
        $this->assertEquals(8.0, (float) $produto->estoque_atual);
        $this->assertDatabaseHas('estoque_movimentacoes', [
            'produto_id' => $produto->id,
            'tipo' => 'SAIDA',
            'motivo' => 'ITEM ADICIONADO NA OS #1',
        ]);
    }

    public function test_os_cannot_be_finished_without_items(): void
    {
        $user = User::factory()->create(['perfil' => 'ADMIN']);

        Livewire::actingAs($user)
            ->test(GerenciarOs::class)
            ->set('forma_pagamento', 'DINHEIRO')
            ->set('status_pagamento', 'PAGO')
            ->set('data_vencimento', now()->toDateString())
            ->call('finalizarOs', false);

        $this->assertDatabaseMissing('ordens_servico', [
            'status' => 'FINALIZADO',
        ]);
    }

    public function test_os_finishes_with_payment_data_and_items(): void
    {
        $user = User::factory()->create(['perfil' => 'ADMIN']);
        $produto = $this->produto(['estoque_atual' => 10, 'preco_venda_prazo' => 45]);

        Livewire::actingAs($user)
            ->test(GerenciarOs::class)
            ->call('adicionarItemDireto', $produto->id, 'produto', $produto->nome, null, 'prazo')
            ->set('forma_pagamento', 'DINHEIRO')
            ->set('status_pagamento', 'PAGO')
            ->set('data_vencimento', now()->toDateString())
            ->call('finalizarOs', false);

        $this->assertDatabaseHas('ordens_servico', [
            'status' => 'FINALIZADO',
            'forma_pagamento' => 'DINHEIRO',
            'status_pagamento' => 'PAGO',
            'valor_total_liquido' => 45,
        ]);
    }

    public function test_cancel_finished_sale_returns_stock(): void
    {
        $user = User::factory()->create(['perfil' => 'ADMIN']);
        $produto = $this->produto(['estoque_atual' => 8, 'preco_venda_prazo' => 45]);

        $ordem = OrdemServico::create([
            'status' => 'FINALIZADO',
            'forma_pagamento' => 'DINHEIRO',
            'status_pagamento' => 'PAGO',
            'valor_total_pecas' => 90,
            'valor_total_liquido' => 90,
            'finalizado_em' => now(),
        ]);

        OsItem::create([
            'ordem_servico_id' => $ordem->id,
            'tipo' => 'PECA',
            'produto_id' => $produto->id,
            'descricao' => $produto->nome,
            'quantidade' => 2,
            'valor_unitario' => 45,
            'desconto_valor' => 0,
            'valor_total' => 90,
        ]);

        Livewire::actingAs($user)
            ->test(GerenciarOs::class)
            ->set('cartaoFechadoSelecionadoId', $ordem->id)
            ->set('motivo_cancelamento', 'ERRO NO LANCAMENTO')
            ->call('cancelarVendaFechada');

        $this->assertDatabaseHas('ordens_servico', [
            'id' => $ordem->id,
            'status' => 'CANCELADO',
            'motivo_cancelamento' => 'ERRO NO LANCAMENTO',
        ]);

        $this->assertEquals(10.0, (float) $produto->fresh()->estoque_atual);
        $this->assertDatabaseHas('estoque_movimentacoes', [
            'produto_id' => $produto->id,
            'tipo' => 'ENTRADA',
            'quantidade' => 2,
        ]);
    }
}
