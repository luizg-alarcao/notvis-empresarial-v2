<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdemServico extends Model
{
    protected $table = 'ordens_servico';
    protected $guarded = [];

    // O.S. pertence a um Cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // O.S. foi aberta por um Atendente (Funcionário)
    public function atendente()
    {
        return $this->belongsTo(Funcionario::class, 'atendente_id');
    }

    // O.S. tem vários Itens (Peças e Serviços)
    public function itens()
    {
        return $this->hasMany(OsItem::class, 'ordem_servico_id');
    }

    // O.S. pode ter vários Mecânicos trabalhando nela (Tabela Pivô)
    public function mecanicos()
    {
        return $this->belongsToMany(Funcionario::class, 'ordem_servico_mecanico', 'ordem_servico_id', 'mecanico_id')
                    ->withTimestamps();
    }
}
