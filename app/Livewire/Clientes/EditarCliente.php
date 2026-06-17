<?php

namespace App\Livewire\Clientes;

use Livewire\Component;
use App\Models\Cliente;
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
        $this->validate([
            'nome' => 'required|min:3',
            'cpf_cnpj' => 'required',
            'whatsapp' => 'required',
            'email' => 'required|email',
        ]);

        $this->cliente->update([
            'nome' => $this->nome,
            'cpf_cnpj' => $this->cpf_cnpj,
            'whatsapp' => $this->whatsapp,
            'email' => $this->email,
            'limite_credito' => $this->limite_credito,
            'cep' => $this->cep,
            'rua' => $this->rua,
            'numero' => $this->numero,
            'bairro' => $this->bairro,
            'cidade' => $this->cidade,
            'estado' => $this->estado,
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
