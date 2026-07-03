<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Livewire\Auth\PrimeiroAcesso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AuthAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_redirects_to_first_access_when_database_has_no_users(): void
    {
        $this->get(route('login'))
            ->assertRedirect(route('primeiro-acesso'));
    }

    public function test_first_access_creates_admin_and_logs_in(): void
    {
        Livewire::test(PrimeiroAcesso::class)
            ->set('name', 'Luiz Gustavo')
            ->set('email', 'admin@notvis.test')
            ->set('password', 'senha12345')
            ->set('password_confirmation', 'senha12345')
            ->call('criarAdministrador')
            ->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'admin@notvis.test',
            'perfil' => 'ADMIN',
            'ativo' => true,
        ]);
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'inativo@notvis.test',
            'password' => Hash::make('senha12345'),
            'perfil' => 'ATENDENTE',
            'ativo' => false,
        ]);

        Livewire::test(Login::class)
            ->set('email', 'inativo@notvis.test')
            ->set('password', 'senha12345')
            ->call('entrar')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_module_routes_respect_user_profile(): void
    {
        $attendant = User::factory()->create(['perfil' => 'ATENDENTE', 'ativo' => true]);
        $stockUser = User::factory()->create(['perfil' => 'ESTOQUE', 'ativo' => true]);

        $this->actingAs($attendant)
            ->get(route('produtos.index'))
            ->assertForbidden();

        $this->actingAs($attendant)
            ->get(route('estoque.movimentacoes'))
            ->assertForbidden();

        $this->actingAs($stockUser)
            ->get(route('produtos.index'))
            ->assertOk();

        $this->actingAs($stockUser)
            ->get(route('estoque.movimentacoes'))
            ->assertOk();
    }
}
