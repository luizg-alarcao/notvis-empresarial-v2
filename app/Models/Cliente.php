<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes'; // Garante que ele aponte para a tabela certa

    protected $fillable = [
        'nome',
        'cpf_cnpj',
        'rg',
        'inscricao_estadual',
        'inscricao_municipal',
        'whatsapp',
        'email',
        'data_nascimento',
        'cidade',
        'estado',
        'cep',
        'rua',
        'numero',
        'bairro',
        'complemento',
        'limite_credito'
    ];
}
