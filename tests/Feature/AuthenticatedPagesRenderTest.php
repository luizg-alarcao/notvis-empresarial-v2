<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticatedPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_render_main_system_pages(): void
    {
        $admin = User::factory()->create([
            'perfil' => 'ADMIN',
            'ativo' => true,
        ]);

        $routes = [
            route('home'),
            route('clientes.index'),
            route('clientes.criar'),
            route('produtos.index'),
            route('produtos.create'),
            route('os.nova'),
            route('estoque.movimentacoes'),
            route('relatorios'),
            route('configuracoes'),
        ];

        foreach ($routes as $route) {
            $this->actingAs($admin)
                ->get($route)
                ->assertOk();
        }
    }

    public function test_guest_is_redirected_from_protected_pages(): void
    {
        $this->get(route('home'))->assertRedirect(route('login'));
        $this->get(route('os.nova'))->assertRedirect(route('login'));
        $this->get(route('relatorios'))->assertRedirect(route('login'));
    }
}
