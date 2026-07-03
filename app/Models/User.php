<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const PERFIS = [
        'ADMIN' => 'Administrador',
        'GERENTE' => 'Gerente',
        'ATENDENTE' => 'Atendente',
        'ESTOQUE' => 'Estoque',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'perfil',
        'ativo',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'ultimo_login_em' => 'datetime',
            'ativo' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function perfilNome(): string
    {
        return self::PERFIS[$this->perfil] ?? $this->perfil ?? 'Usuario';
    }

    public function isAdmin(): bool
    {
        return $this->perfil === 'ADMIN';
    }

    public function podeAcessar(string $modulo): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return match ($modulo) {
            'clientes', 'os' => in_array($this->perfil, ['GERENTE', 'ATENDENTE'], true),
            'produtos', 'estoque' => in_array($this->perfil, ['GERENTE', 'ESTOQUE'], true),
            'relatorios' => $this->perfil === 'GERENTE',
            'configuracoes', 'usuarios' => false,
            default => false,
        };
    }
}
