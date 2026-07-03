<?php

namespace App\Livewire\Clientes;

use Livewire\Component;
use App\Models\Cliente;
use App\Models\AuditLog;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;

class EditarCliente extends Component
{
    public Cliente $cliente; // O objeto cliente que vamos editar

    // Campos que podem ser editados
    public $nome, $cpf_cnpj, $whatsapp, $email, $limite_credito;
    public $cep, $rua, $numero, $bairro, $cidade, $estado;

    public function mount(Cliente $cliente)
    {
        $this->cliente = $cliente;

        // Preenche os campos do formulário com o que já está no banco
        $this->nome = $cliente->nome;
        $this->cpf_cnpj = $cliente->cpf_cnpj;
        $this->whatsapp = $cliente->whatsapp;
        $this->email = $cliente->email;
        $this->limite_credito = $cliente->limite_credito;
        $this->cep = $cliente->cep;
        $this->rua = $cliente->rua;
        $this->numero = $cliente->numero;
        $this->bairro = $cliente->bairro;
        $this->cidade = $cliente->cidade;
        $this->estado = $cliente->estado;
    }

    // Sempre que o campo 'nome' for alterado, transforma em MAIÚSCULAS
    public function updatedNome($value)
    {
        $this->nome = mb_strtoupper($value);
    }

    public function salvar()
    {
        $docLimpo = Cliente::limparDocumento($this->cpf_cnpj);
        $whatsappLimpo = preg_replace('/[^0-9]/', '', (string) $this->whatsapp);
        $this->cpf_cnpj = $docLimpo;
        $this->whatsapp = $whatsappLimpo;

        $this->validate([
            'nome' => 'required|min:3',
            'cpf_cnpj' => [
                'required',
                Rule::unique('clientes', 'cpf_cnpj')->ignore($this->cliente->id),
            ],
            'whatsapp' => 'required|digits_between:10,11',
            'email' => 'required|email',
            'limite_credito' => 'nullable|numeric|min:0',
        ]);

        if (!Cliente::documentoValido($docLimpo)) {
            $this->addError('cpf_cnpj', 'Informe um CPF ou CNPJ valido.');
            return;
        }

        $this->cliente->update([
            'nome' => $this->nome,
            'cpf_cnpj' => $docLimpo,
            'whatsapp' => $whatsappLimpo,
            'email' => $this->email,
            'limite_credito' => $this->limite_credito,
            'cep' => $this->cep,
            'rua' => $this->rua,
            'numero' => $this->numero,
            'bairro' => $this->bairro,
            'cidade' => $this->cidade,
            'estado' => $this->estado,
        ]);

        AuditLog::registrar('clientes', 'cliente_atualizado', 'Cadastro do cliente atualizado.', $this->cliente, [
            'nome' => $this->cliente->nome,
            'documento' => $this->cliente->cpf_cnpj,
        ]);

        return redirect()->route('clientes.index')
            ->with('success', 'Cadastro atualizado com sucesso!');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.clientes.editar-cliente');
    }
}
