<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Clientes\CriarCliente;
use App\Livewire\Clientes\ListarClientes;
use App\Livewire\Clientes\EditarCliente;
use App\Livewire\Clientes\VisualizarCliente;
use App\Livewire\Produtos\ListarProdutos;
use App\Livewire\Produtos\CriarProduto;
use App\Livewire\Produtos\EditarProduto;
use App\Livewire\Home;
use App\Livewire\OrdemServico\GerenciarOs;

// Tela inicial do Laravel (pode manter ou apagar depois)
Route::get('/', function () {
    return view('welcome');
});

Route::get('/', Home::class)->name('home');

// Rota de Cadastro (Adicione o ->name no final)
Route::get('/clientes/novo', CriarCliente::class)->name('clientes.criar');

// Rota de Listagem
Route::get('/clientes', ListarClientes::class)->name('clientes.index');

// Rota de Edição (Adicione o ->name no final)
Route::get('/clientes/editar/{cliente}', EditarCliente::class)->name('clientes.editar');

// Rota de Visualização (Adicione o ->name no final)
Route::get('/clientes/ver/{cliente}', VisualizarCliente::class)->name('clientes.show');

// Rotas de Produtos / Serviços
Route::get('/produtos', ListarProdutos::class)->name('produtos.index');
Route::get('/produtos/novo', CriarProduto::class)->name('produtos.create');
Route::get('/produtos/editar/{id}', EditarProduto::class)->name('produtos.edit');

// Rota para CRIAR uma nova O.S. (O ID vai vazio e o Livewire cria o rascunho)
Route::get('/os/nova', GerenciarOs::class)->name('os.nova');

// Rota para EDITAR ou continuar uma O.S. existente
Route::get('/os/{id}', GerenciarOs::class)->name('os.editar');
