<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    protected $table = 'funcionarios';

    // Libera todos os campos para serem salvos em massa
    protected $guarded = [];

    public function ordensAtendidas()
    {
        return $this->hasMany(OrdemServico::class, 'atendente_id');
    }

    public function ordensMecanico()
    {
        return $this->belongsToMany(OrdemServico::class, 'ordem_servico_mecanico', 'mecanico_id', 'ordem_servico_id');
    }
}
