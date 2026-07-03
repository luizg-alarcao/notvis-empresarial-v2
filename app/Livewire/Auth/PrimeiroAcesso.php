<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PrimeiroAcesso extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount()
    {
        if (User::query()->exists()) {
            return redirect()->route('login');
        }
    }

    public function criarAdministrador()
    {
        if (User::query()->exists()) {
            return redirect()->route('login');
        }

        $dados = $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Informe o nome do administrador.',
            'name.min' => 'O nome precisa ter pelo menos 3 caracteres.',
            'email.required' => 'Informe o e-mail.',
            'email.email' => 'Informe um e-mail valido.',
            'password.required' => 'Informe a senha.',
            'password.min' => 'A senha precisa ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmacao da senha nao confere.',
        ]);

        $user = User::create([
            'name' => mb_strtoupper($dados['name'], 'UTF-8'),
            'email' => mb_strtolower($dados['email'], 'UTF-8'),
            'password' => $dados['password'],
            'perfil' => 'ADMIN',
            'ativo' => true,
            'ultimo_login_em' => now(),
        ]);

        Auth::login($user);
        if (request()->hasSession()) {
            request()->session()->regenerate();
        }
        AuditLog::registrar('autenticacao', 'primeiro_acesso', 'Administrador principal criado no primeiro acesso.', $user);

        return redirect()->route('home');
    }

    #[Layout('layouts.auth')]
    public function render()
    {
        return view('livewire.auth.primeiro-acesso');
    }
}
