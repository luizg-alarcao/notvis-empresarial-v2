<?php

namespace Tests\Feature;

use App\Livewire\Estoque\Movimentacoes;
use App\Models\Produto;
use App\Models\User;
use App\Services\EstoqueMovimentacaoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Livewire\Livewire;
use Tests\TestCase;

class EstoqueMovimentacaoTest extends TestCase
{
    use RefreshDatabase;

    private function produto(array $dados = []): Produto
    {
        return Produto::create(array_merge([
            'nome' => 'Lampada H4 Teste',
            'tipo' => 'P',
            'codigo_interno' => null,
            'codigo_barras' => null,
            'unidade' => 'UN',
            'marca' => 'TESTE',
            'categoria' => 'AUTO ELETRICA',
            'preco_custo' => 10,
            'margem_lucro' => 50,
            'preco_venda_vista' => 14.25,
            'preco_venda_prazo' => 15,
            'estoque_atual' => 10,
            'estoque_minimo' => 2,
            'controla_estoque' => true,
            'ativo' => true,
        ], $dados));
    }

    public function test_stock_entry_updates_product_and_registers_history(): void
    {
        $user = User::factory()->create(['perfil' => 'ADMIN']);
        $produto = $this->produto(['estoque_atual' => 10]);

        $this->actingAs($user);

        app(EstoqueMovimentacaoService::class)->registrar($produto, 'ENTRADA', 5, 'Compra de mercadoria');

        $this->assertEquals(15.0, (float) $produto->fresh()->estoque_atual);
        $this->assertDatabaseHas('estoque_movimentacoes', [
            'produto_id' => $produto->id,
            'user_id' => $user->id,
            'tipo' => 'ENTRADA',
            'motivo' => 'COMPRA DE MERCADORIA',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'modulo' => 'ESTOQUE',
            'acao' => 'ENTRADA',
        ]);
    }

    public function test_stock_output_cannot_make_stock_negative(): void
    {
        $user = User::factory()->create(['perfil' => 'ADMIN']);
        $produto = $this->produto(['estoque_atual' => 1]);

        $this->actingAs($user);

        try {
            app(EstoqueMovimentacaoService::class)->registrar($produto, 'SAIDA', 2, 'Perda');
            $this->fail('A saida acima do estoque deveria ser bloqueada.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('Estoque insuficiente para esta movimentacao.', $exception->getMessage());
        }

        $this->assertEquals(1.0, (float) $produto->fresh()->estoque_atual);
        $this->assertDatabaseMissing('estoque_movimentacoes', [
            'produto_id' => $produto->id,
            'tipo' => 'SAIDA',
        ]);
    }

    public function test_stock_adjustment_sets_final_quantity(): void
    {
        $user = User::factory()->create(['perfil' => 'ADMIN']);
        $produto = $this->produto(['estoque_atual' => 10]);

        $this->actingAs($user);

        app(EstoqueMovimentacaoService::class)->registrar($produto, 'AJUSTE', 7.5, 'Conferencia fisica');

        $this->assertEquals(7.5, (float) $produto->fresh()->estoque_atual);
        $this->assertDatabaseHas('estoque_movimentacoes', [
            'produto_id' => $produto->id,
            'tipo' => 'AJUSTE',
            'motivo' => 'CONFERENCIA FISICA',
        ]);
    }

    public function test_stock_screen_registers_manual_output(): void
    {
        $user = User::factory()->create(['perfil' => 'ESTOQUE']);
        $produto = $this->produto(['estoque_atual' => 10]);

        Livewire::actingAs($user)
            ->test(Movimentacoes::class)
            ->set('produto_id', (string) $produto->id)
            ->call('selecionarTipo', 'SAIDA')
            ->set('quantidade', '2')
            ->set('motivo', 'avaria')
            ->call('registrarMovimentacao')
            ->assertHasNoErrors();

        $this->assertEquals(8.0, (float) $produto->fresh()->estoque_atual);
        $this->assertDatabaseHas('estoque_movimentacoes', [
            'produto_id' => $produto->id,
            'tipo' => 'SAIDA',
            'motivo' => 'AVARIA',
        ]);
    }

    public function test_stock_profile_can_access_stock_movement_route(): void
    {
        $user = User::factory()->create(['perfil' => 'ESTOQUE']);

        $this->actingAs($user)
            ->get(route('estoque.movimentacoes'))
            ->assertOk()
            ->assertSee('Movimentação de Estoque');
    }
}
