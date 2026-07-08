<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\PrimeiroAcesso;
use App\Livewire\Clientes\CriarCliente;
use App\Livewire\Clientes\EditarCliente;
use App\Livewire\Clientes\ListarClientes;
use App\Livewire\Clientes\VisualizarCliente;
use App\Livewire\Configuracoes;
use App\Livewire\Estoque\Movimentacoes as MovimentacoesEstoque;
use App\Livewire\Home;
use App\Livewire\OrdemServico\GerenciarOs;
use App\Livewire\Produtos\CriarProduto;
use App\Livewire\Produtos\EditarProduto;
use App\Livewire\Produtos\ListarProdutos;
use App\Livewire\Relatorios;
use App\Models\AuditLog;
use App\Models\Empresa;
use App\Models\OrdemServico;
use App\Support\DemoDataSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/login', Login::class)->middleware('guest')->name('login');
Route::get('/primeiro-acesso', PrimeiroAcesso::class)->middleware('guest')->name('primeiro-acesso');

Route::post('/logout', function () {
    if (Auth::check()) {
        AuditLog::registrar('autenticacao', 'logout', 'Usuario saiu do sistema.', Auth::user());
    }

    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', Home::class)->name('home');

    Route::middleware('modulo:clientes')->group(function () {
        Route::get('/clientes', ListarClientes::class)->name('clientes.index');
        Route::get('/clientes/novo', CriarCliente::class)->name('clientes.criar');
        Route::get('/clientes/editar/{cliente}', EditarCliente::class)->name('clientes.editar');
        Route::get('/clientes/ver/{cliente}', VisualizarCliente::class)->name('clientes.show');
    });

    Route::middleware('modulo:produtos')->group(function () {
        Route::get('/produtos', ListarProdutos::class)->name('produtos.index');
        Route::get('/produtos/novo', CriarProduto::class)->name('produtos.create');
        Route::get('/produtos/editar/{id}', EditarProduto::class)->name('produtos.edit');
    });

    Route::get('/estoque/movimentacoes', MovimentacoesEstoque::class)
        ->middleware('modulo:estoque')
        ->name('estoque.movimentacoes');

    Route::get('/configuracoes', Configuracoes::class)
        ->middleware('modulo:configuracoes')
        ->name('configuracoes');

    Route::get('/relatorios', Relatorios::class)
        ->middleware('modulo:relatorios')
        ->name('relatorios');

    Route::middleware('modulo:os')->group(function () {
        Route::get('/os/nova', GerenciarOs::class)->name('os.nova');

        Route::get('/os/{ordemServico}/comprovante', function (OrdemServico $ordemServico) {
            $ordemServico->load(['cliente', 'atendente', 'itens.produto']);
            $tipoDocumento = request('tipo');
            $proximaOsAberta = OrdemServico::where('status', 'RASCUNHO')
                ->where('id', '!=', $ordemServico->id)
                ->latest()
                ->first();

            if (!in_array($tipoDocumento, ['orcamento', 'comprovante'], true)) {
                $tipoDocumento = $ordemServico->status === 'FINALIZADO' ? 'comprovante' : 'orcamento';
            }

            return view('ordem-servico.comprovante', [
                'os' => $ordemServico,
                'empresa' => Empresa::first(),
                'tipoDocumento' => $tipoDocumento,
                'urlVoltarOs' => $proximaOsAberta
                    ? route('os.editar', $proximaOsAberta->id)
                    : route('os.nova'),
            ]);
        })->name('os.comprovante');

        Route::get('/os/{id}', GerenciarOs::class)->name('os.editar');
    });

    Route::get('/demo/preparar-dados', function (DemoDataSeeder $seeder) {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $resumo = $seeder->run(Auth::user());

        return response()->view('demo.preparar-dados', [
            'resumo' => $resumo,
        ]);
    })->name('demo.preparar-dados');
});
