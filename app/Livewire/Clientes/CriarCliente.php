<?php

namespace App\Livewire\Clientes;

use Livewire\Component;
use App\Models\Cliente;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;

class CriarCliente extends Component
{
    // Seus campos originais
    public $nome, $cpf_cnpj, $rg, $inscricao_estadual, $inscricao_municipal;
    public $whatsapp, $email, $data_nascimento;
    public $cep, $rua, $numero, $bairro, $complemento, $cidade, $estado;

    public $isentoIE = false;

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.clientes.criar-cliente');
    }

    public function updatedCep($valor)
    {
        $cep = preg_replace('/[^0-9]/', '', $valor);

        if (strlen($cep) === 8) {
            try {
                $response = Http::timeout(5)->get("https://viacep.com.br/ws/{$cep}/json/")->json();
            } catch (\Throwable) {
                return;
            }

            if (!isset($response['erro'])) {
                $this->rua = $response['logradouro'];
                $this->bairro = $response['bairro'];
                $this->cidade = $response['localidade'];
                $this->estado = $response['uf'];
            }
        }
    }

    public function updatedIsentoIE($valor)
    {
        if ($valor) {
            $this->inscricao_estadual = 'ISENTO';
        } else {
            $this->inscricao_estadual = '';
        }
    }

    // Sempre que o campo 'nome' for alterado, transforma em MAIÚSCULAS
    public function updatedNome($value)
    {
        $this->nome = mb_strtoupper($value);
    }

    public function salvar()
    {
        // Limpando caracteres para salvar apenas números (exceto e-mail e nomes)
        $docLimpo = Cliente::limparDocumento($this->cpf_cnpj);
        $whatsLimpo = preg_replace('/[^0-9]/', '', $this->whatsapp);
        $this->rg = preg_replace('/[^0-9]/', '', $this->rg);
        $this->cep = preg_replace('/[^0-9]/', '', $this->cep);
        $this->cpf_cnpj = $docLimpo;
        $this->whatsapp = $whatsLimpo;
        if (!$this->isentoIE) {
            $this->inscricao_estadual = preg_replace('/[^0-9]/', '', $this->inscricao_estadual);
        }

        // Suas regras com as mensagens em Português que você pediu
        $this->validate([
            'nome' => 'required|min:3',
            'whatsapp' => 'required|digits_between:10,11',
            'email' => 'required|email',
            'cpf_cnpj' => 'required|unique:clientes,cpf_cnpj',
        ], [
            'nome.required' => 'O nome é obrigatório.',
            'whatsapp.required' => 'O WhatsApp é obrigatório.',
            'whatsapp.digits_between' => 'O WhatsApp deve ter DDD e número.',
            'whatsapp.min' => 'O WhatsApp deve ter o DDD e o número.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Insira um e-mail válido.',
            'cpf_cnpj.required' => 'O CPF/CNPJ é obrigatório.',
            'cpf_cnpj.unique' => 'Este documento já está cadastrado.',
        ]);

        // Validação de tamanho que você solicitou
        if (!Cliente::documentoValido($docLimpo)) {
            $this->addError('cpf_cnpj', 'Informe um CPF ou CNPJ valido.');
            return;
        }

        $cliente = Cliente::create([
            'nome' => $this->nome,
            'cpf_cnpj' => $docLimpo,
            'rg' => $this->rg,
            'inscricao_estadual' => $this->inscricao_estadual,
            'inscricao_municipal' => $this->inscricao_municipal,
            'whatsapp' => $whatsLimpo,
            'email' => $this->email,
            'data_nascimento' => $this->data_nascimento,
            'cep' => $this->cep,
            'rua' => $this->rua,
            'numero' => $this->numero,
            'bairro' => $this->bairro,
            'complemento' => $this->complemento,
            'cidade' => $this->cidade,
            'estado' => $this->estado,
        ]);

        AuditLog::registrar('clientes', 'cliente_criado', 'Cliente cadastrado.', $cliente, [
            'nome' => $cliente->nome,
            'documento' => $cliente->cpf_cnpj,
        ]);

        //Código antigo que usava flash e reset, mas eu achei melhor redirecionar para a página de listagem, comentei ele.
        //Se quiser usar o flash sem redirecionar, é só descomentar e remover o redirect.
        /*session()->flash('success', 'Cliente cadastrado com sucesso!');
        $this->reset();
        $this->isentoIE = false; */

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente cadastrado com sucesso!');
    }
}
