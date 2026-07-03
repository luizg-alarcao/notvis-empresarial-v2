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

    public function ordensServico()
    {
        return $this->hasMany(OrdemServico::class, 'cliente_id');
    }

    public static function limparDocumento($valor): string
    {
        return preg_replace('/[^0-9]/', '', (string) $valor);
    }

    public static function documentoValido($valor): bool
    {
        $documento = self::limparDocumento($valor);

        return match (strlen($documento)) {
            11 => self::cpfValido($documento),
            14 => self::cnpjValido($documento),
            default => false,
        };
    }

    private static function cpfValido(string $cpf): bool
    {
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($posicao = 9; $posicao < 11; $posicao++) {
            $soma = 0;
            for ($i = 0; $i < $posicao; $i++) {
                $soma += (int) $cpf[$i] * (($posicao + 1) - $i);
            }

            $digito = ((10 * $soma) % 11) % 10;
            if ((int) $cpf[$posicao] !== $digito) {
                return false;
            }
        }

        return true;
    }

    private static function cnpjValido(string $cnpj): bool
    {
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $multiplicadoresPrimeiro = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $multiplicadoresSegundo = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $soma = 0;
        for ($i = 0; $i < 12; $i++) {
            $soma += (int) $cnpj[$i] * $multiplicadoresPrimeiro[$i];
        }

        $resto = $soma % 11;
        $primeiroDigito = $resto < 2 ? 0 : 11 - $resto;
        if ((int) $cnpj[12] !== $primeiroDigito) {
            return false;
        }

        $soma = 0;
        for ($i = 0; $i < 13; $i++) {
            $soma += (int) $cnpj[$i] * $multiplicadoresSegundo[$i];
        }

        $resto = $soma % 11;
        $segundoDigito = $resto < 2 ? 0 : 11 - $resto;

        return (int) $cnpj[13] === $segundoDigito;
    }
}
