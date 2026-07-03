<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_registers_action_with_authenticated_user(): void
    {
        $user = User::factory()->create(['perfil' => 'ADMIN']);

        $this->actingAs($user);

        AuditLog::registrar('teste', 'acao_teste', 'Registro de teste.', $user, [
            'campo' => 'valor',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'modulo' => 'TESTE',
            'acao' => 'ACAO_TESTE',
            'descricao' => 'Registro de teste.',
            'entidade_id' => $user->id,
        ]);
    }
}
