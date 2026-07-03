<?php

namespace Tests\Feature;

use App\Livewire\Configuracoes;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ConfiguracoesSegurancaTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_save_requires_admin_password(): void
    {
        $admin = User::factory()->create([
            'perfil' => 'ADMIN',
            'ativo' => true,
            'password' => Hash::make('senha12345'),
        ]);

        Livewire::actingAs($admin)
            ->test(Configuracoes::class)
            ->set('nome_fantasia', 'Auto Eletrica Roseira')
            ->set('razao_social', 'Auto Eletrica e Acessorios Roseira Ltda')
            ->set('cnpj', '45.723.174/0001-10')
            ->set('telefone', '(44) 99999-9999')
            ->set('email', 'empresa@teste.com')
            ->set('endereco', 'PR 323, KM 254')
            ->call('salvarEmpresa')
            ->set('senha_admin', 'senha-errada')
            ->call('confirmarAcaoAdmin')
            ->assertHasErrors(['senha_admin']);

        $this->assertDatabaseMissing('empresas', [
            'cnpj' => '45723174000110',
        ]);
    }

    public function test_company_save_with_admin_password_validates_and_persists_data(): void
    {
        $admin = User::factory()->create([
            'perfil' => 'ADMIN',
            'ativo' => true,
            'password' => Hash::make('senha12345'),
        ]);

        Livewire::actingAs($admin)
            ->test(Configuracoes::class)
            ->set('nome_fantasia', 'Auto Eletrica Roseira')
            ->set('razao_social', 'Auto Eletrica e Acessorios Roseira Ltda')
            ->set('cnpj', '45.723.174/0001-10')
            ->set('telefone', '(44) 99999-9999')
            ->set('email', 'empresa@teste.com')
            ->set('endereco', 'PR 323, KM 254')
            ->call('salvarEmpresa')
            ->set('senha_admin', 'senha12345')
            ->call('confirmarAcaoAdmin')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('empresas', [
            'nome_fantasia' => 'AUTO ELETRICA ROSEIRA',
            'razao_social' => 'AUTO ELETRICA E ACESSORIOS ROSEIRA LTDA',
            'cnpj' => '45723174000110',
            'telefone' => '44999999999',
            'email' => 'empresa@teste.com',
        ]);
    }

    public function test_logged_admin_cannot_delete_own_user(): void
    {
        $admin = User::factory()->create([
            'perfil' => 'ADMIN',
            'ativo' => true,
            'password' => Hash::make('senha12345'),
        ]);

        Livewire::actingAs($admin)
            ->test(Configuracoes::class)
            ->call('excluirUsuario', $admin->id)
            ->set('senha_admin', 'senha12345')
            ->call('confirmarAcaoAdmin');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'ativo' => true,
        ]);
    }
}
