<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\OrdemServico;
use App\Models\Produto;
use App\Models\User;
use App\Support\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_data_seeder_creates_presentation_data(): void
    {
        $admin = User::factory()->create([
            'perfil' => 'ADMIN',
            'ativo' => true,
        ]);

        app(DemoDataSeeder::class)->run($admin);

        $this->assertGreaterThanOrEqual(6, Cliente::count());
        $this->assertGreaterThanOrEqual(10, Produto::where('tipo', 'P')->count());
        $this->assertGreaterThanOrEqual(5, OrdemServico::count());
        $this->assertDatabaseHas('users', ['email' => 'admin@notvis.com', 'perfil' => 'ADMIN']);
    }
}
