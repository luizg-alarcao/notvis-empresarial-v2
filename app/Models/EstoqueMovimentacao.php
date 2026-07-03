<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstoqueMovimentacao extends Model
{
    protected $table = 'estoque_movimentacoes';

    protected $fillable = [
        'produto_id',
        'user_id',
        'tipo',
        'quantidade',
        'estoque_anterior',
        'estoque_posterior',
        'motivo',
        'observacao',
        'origem',
        'origem_id',
    ];

    protected $casts = [
        'quantidade' => 'decimal:3',
        'estoque_anterior' => 'decimal:3',
        'estoque_posterior' => 'decimal:3',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tipoNome(): string
    {
        return match ($this->tipo) {
            'ENTRADA' => 'Entrada',
            'SAIDA' => 'Saída',
            'AJUSTE' => 'Ajuste',
            default => $this->tipo,
        };
    }
}
