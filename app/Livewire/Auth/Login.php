<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function mount()
    {
        if (!User::query()->exists()) {
            return redirect()->route('primeiro-acesso');
        }

        if (Auth::check()) {
            return redirect()->route('home');
        }
    }

    public function entrar()
    {
        $dados = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Informe o e-mail.',
            'email.email' => 'Informe um e-mail valido.',
            'password.required' => 'Informe a senha.',
        ]);

        $email = mb_strtolower($dados['email'], 'UTF-8');
        $user = User::where('email', $email)->first();

        if ($user && !$user->ativo) {
            $this->addError('email', 'Este usuario esta inativo.');
            return;
        }

        if (!Auth::attempt(['email' => $email, 'password' => $dados['password'], 'ativo' => true], $this->remember)) {
            $this->addError('email', 'E-mail ou senha invalidos.');
            return;
        }

        if (request()->hasSession()) {
            request()->session()->regenerate();
        }
        Auth::user()->forceFill(['ultimo_login_em' => now()])->save();
        AuditLog::registrar('autenticacao', 'login', 'Usuario entrou no sistema.', Auth::user());

        return redirect()->intended(route('home'));
    }

    #[Layout('layouts.auth')]
    public function render()
    {
        return view('livewire.auth.login');
    }
}
