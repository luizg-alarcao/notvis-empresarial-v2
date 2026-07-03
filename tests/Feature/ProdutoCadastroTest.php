<?php

namespace Tests\Feature;

use App\Livewire\Produtos\CriarProduto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ProdutoCadastroTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_registration_validates_required_fields(): void
    {
        $user = User::factory()->create(['perfil' => 'ADMIN']);

        Livewire::actingAs($user)
            ->test(CriarProduto::class)
            ->call('salvar')
            ->assertHasErrors(['nome', 'preco_custo', 'preco_venda_vista', 'preco_venda_prazo']);
    }

    public function test_product_registration_saves_valid_product(): void
    {
        $user = User::factory()->create(['perfil' => 'ADMIN']);

        Livewire::actingAs($user)
            ->test(CriarProduto::class)
            ->set('nome', 'Lampada H4 24V')
            ->set('marca', 'Philips')
            ->set('categoria', 'ELETRICA')
            ->set('tipo', 'P')
            ->set('unidade', 'UN')
            ->set('codigo_barras', '7891234567890')
            ->set('preco_custo', 40)
            ->set('margem_lucro', 50)
            ->set('preco_venda_vista', 57)
            ->set('preco_venda_prazo', 60)
            ->set('estoque_atual', 10)
            ->set('estoque_minimo', 2)
            ->set('cfop', '5102')
            ->set('cst_csosn', '102')
            ->set('origem', 0)
            ->call('salvar')
            ->assertRedirect(route('produtos.index'));

        $this->assertDatabaseHas('produtos', [
            'nome' => 'LAMPADA H4 24V',
            'codigo_barras' => '7891234567890',
            'estoque_atual' => 10,
        ]);
    }

    public function test_barcode_lookup_uses_configured_token_without_exposing_it_in_source(): void
    {
        $source = file_get_contents(app_path('Livewire/Produtos/CriarProduto.php'));
        $this->assertStringNotContainsString("'X-Cosmos-Token' => '", $source);

        config(['notvis.cosmos_token' => 'fake-token']);

        Http::fake([
            'api.cosmos.bluesoft.com.br/*' => Http::response([
                'description' => 'Produto via API',
                'brand' => ['name' => 'Marca API'],
            ], 200),
        ]);

        $user = User::factory()->create(['perfil' => 'ADMIN']);

        Livewire::actingAs($user)
            ->test(CriarProduto::class)
            ->set('codigo_barras', '7891234567890')
            ->assertSet('nome', 'PRODUTO VIA API')
            ->assertSet('marca', 'MARCA API');

        Http::assertSent(fn ($request) => $request->hasHeader('X-Cosmos-Token', 'fake-token'));
    }
}
