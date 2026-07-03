<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome', 'tipo', 'codigo_interno', 'codigo_barras', 'unidade',
        'marca', 'categoria', 'descricao_detalhada',
        'preco_custo', 'margem_lucro', 'preco_venda_vista', 'preco_venda_prazo',
        'preco_minimo', 'preco_venda_vista_desconto', 'permite_desconto',
        'estoque_atual', 'estoque_minimo', 'estoque_maximo', 'controla_estoque',
        'localizacao', 'lote', 'data_validade',
        'ncm', 'cfop', 'cst_csosn', 'origem', 'aliquota_icms',
        'ativo'
    ];

    protected $casts = [
        'preco_custo' => 'decimal:2',
        'margem_lucro' => 'decimal:2',
        'preco_venda_vista' => 'decimal:2',
        'preco_venda_prazo' => 'decimal:2',
        'preco_venda_vista_desconto' => 'decimal:2',
        'estoque_atual' => 'decimal:3',
        'estoque_minimo' => 'decimal:3',
        'estoque_maximo' => 'decimal:3',
        'controla_estoque' => 'boolean',
        'ativo' => 'boolean',
    ];

    public function movimentacoesEstoque()
    {
        return $this->hasMany(EstoqueMovimentacao::class, 'produto_id');
    }
}
