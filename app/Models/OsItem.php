<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OsItem extends Model
{
    protected $table = 'os_itens';
    protected $guarded = [];

    // Um item sempre pertence a uma O.S.
    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    // Se for peça, ele se liga ao seu cadastro de Produtos
    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class, 'servico_id');
    }
}

