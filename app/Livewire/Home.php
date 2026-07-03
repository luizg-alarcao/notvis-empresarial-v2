<?php

namespace App\Livewire;

use App\Models\Empresa;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        $empresa = Schema::hasTable('empresas') ? Empresa::query()->first() : null;
        $empresaAtiva = $empresa?->razao_social ?: $empresa?->nome_fantasia ?: 'EMPRESA NAO CONFIGURADA';

        // Aqui definimos que ele vai usar o seu layout principal (app.blade.php)
        return view('livewire.home', [
            'empresaAtiva' => $empresaAtiva,
        ])->layout('layouts.app');
    }
}
