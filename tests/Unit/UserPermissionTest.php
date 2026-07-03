<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserPermissionTest extends TestCase
{
    public function test_admin_can_access_all_main_modules(): void
    {
        $user = new User(['perfil' => 'ADMIN', 'ativo' => true]);

        $this->assertTrue($user->podeAcessar('clientes'));
        $this->assertTrue($user->podeAcessar('os'));
        $this->assertTrue($user->podeAcessar('produtos'));
        $this->assertTrue($user->podeAcessar('estoque'));
        $this->assertTrue($user->podeAcessar('relatorios'));
        $this->assertTrue($user->podeAcessar('configuracoes'));
    }

    public function test_attendant_does_not_access_restricted_modules(): void
    {
        $user = new User(['perfil' => 'ATENDENTE', 'ativo' => true]);

        $this->assertTrue($user->podeAcessar('clientes'));
        $this->assertTrue($user->podeAcessar('os'));
        $this->assertFalse($user->podeAcessar('produtos'));
        $this->assertFalse($user->podeAcessar('estoque'));
        $this->assertFalse($user->podeAcessar('relatorios'));
        $this->assertFalse($user->podeAcessar('configuracoes'));
    }

    public function test_stock_profile_accesses_stock_and_products_only(): void
    {
        $user = new User(['perfil' => 'ESTOQUE', 'ativo' => true]);

        $this->assertTrue($user->podeAcessar('produtos'));
        $this->assertTrue($user->podeAcessar('estoque'));
        $this->assertFalse($user->podeAcessar('clientes'));
        $this->assertFalse($user->podeAcessar('os'));
        $this->assertFalse($user->podeAcessar('relatorios'));
        $this->assertFalse($user->podeAcessar('configuracoes'));
    }
}
