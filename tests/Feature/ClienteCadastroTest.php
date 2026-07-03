<?php

namespace Tests\Feature;

use App\Livewire\Clientes\CriarCliente;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClienteCadastroTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_registration_requires_valid_document(): void
    {
        $user = User::factory()->create(['perfil' => 'ADMIN']);

        Livewire::actingAs($user)
            ->test(CriarCliente::class)
            ->set('nome', 'Cliente Teste')
            ->set('cpf_cnpj', '11111111111')
            ->set('whatsapp', '44999999999')
            ->set('email', 'cliente@teste.com')
            ->call('salvar')
            ->assertHasErrors(['cpf_cnpj']);

        $this->assertDatabaseMissing('clientes', [
            'email' => 'cliente@teste.com',
        ]);
    }

    public function test_customer_registration_cleans_document_and_phone(): void
    {
        $user = User::factory()->create(['perfil' => 'ADMIN']);

        Livewire::actingAs($user)
            ->test(CriarCliente::class)
            ->set('nome', 'Cliente Valido')
            ->set('cpf_cnpj', '529.982.247-25')
            ->set('whatsapp', '(44) 99999-9999')
            ->set('email', 'cliente.valido@teste.com')
            ->call('salvar')
            ->assertRedirect(route('clientes.index'));

        $this->assertDatabaseHas('clientes', [
            'cpf_cnpj' => '52998224725',
            'whatsapp' => '44999999999',
            'email' => 'cliente.valido@teste.com',
        ]);
    }

    public function test_customer_document_validator_accepts_valid_cnpj(): void
    {
        $this->assertTrue(Cliente::documentoValido('45.723.174/0001-10'));
    }
}
