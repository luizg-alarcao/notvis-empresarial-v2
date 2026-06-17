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
        'preco_minimo', 'permite_desconto',
        'estoque_atual', 'estoque_minimo', 'estoque_maximo', 'controla_estoque',
        'localizacao', 'lote', 'data_validade',
        'ncm', 'cfop', 'cst_csosn', 'origem', 'aliquota_icms',
        'ativo'
    ];
}
