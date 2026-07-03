<?php

namespace Tests\Feature;

use App\Livewire\Relatorios;
use App\Models\Cliente;
use App\Models\OrdemServico;
use App\Models\OsItem;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RelatoriosTest extends TestCase
{
    use RefreshDatabase;

    private function criarVendaFinalizada(): void
    {
        $cliente = Cliente::create([
            'nome' => 'CLIENTE RELATORIO',
            'cpf_cnpj' => '52998224725',
            'whatsapp' => '44999999999',
            'email' => 'cliente.relatorio@teste.com',
        ]);

        $produto = Produto::create([
            'nome' => 'LAMPADA RELATORIO',
            'tipo' => 'P',
            'unidade' => 'UN',
            'categoria' => 'ELETRICA',
            'preco_custo' => 20,
            'preco_venda_vista' => 28.5,
            'preco_venda_prazo' => 30,
            'estoque_atual' => 5,
            'estoque_minimo' => 1,
            'controla_estoque' => true,
            'ativo' => true,
        ]);

        $ordem = OrdemServico::create([
            'cliente_id' => $cliente->id,
            'status' => 'FINALIZADO',
            'forma_pagamento' => 'PIX',
            'status_pagamento' => 'PAGO',
            'valor_total_pecas' => 30,
            'valor_total_liquido' => 30,
            'finalizado_em' => now(),
        ]);

        OsItem::create([
            'ordem_servico_id' => $ordem->id,
            'tipo' => 'PECA',
            'produto_id' => $produto->id,
            'descricao' => $produto->nome,
            'quantidade' => 1,
            'valor_unitario' => 30,
            'desconto_valor' => 0,
            'valor_total' => 30,
        ]);
    }

    public function test_manager_can_open_reports_with_closed_cards(): void
    {
        $user = User::factory()->create(['perfil' => 'GERENTE', 'ativo' => true]);
        $this->criarVendaFinalizada();

        $this->actingAs($user)
            ->get(route('relatorios'))
            ->assertOk()
            ->assertSee('CLIENTE RELATORIO')
            ->assertSee('R$ 30,00');
    }

    public function test_reports_component_switches_between_tabs(): void
    {
        $user = User::factory()->create(['perfil' => 'ADMIN', 'ativo' => true]);
        $this->criarVendaFinalizada();

        Livewire::actingAs($user)
            ->test(Relatorios::class)
            ->assertSet('aba', 'fechamentos')
            ->call('setAba', 'produtos')
            ->assertSet('aba', 'produtos')
            ->assertSee('LAMPADA RELATORIO')
            ->call('setAba', 'clientes')
            ->assertSet('aba', 'clientes')
            ->assertSee('CLIENTE RELATORIO');
    }
}
