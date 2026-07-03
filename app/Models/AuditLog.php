<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'modulo',
        'acao',
        'descricao',
        'entidade_tipo',
        'entidade_id',
        'dados',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'dados' => 'array',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function registrar(
        string $modulo,
        string $acao,
        string $descricao,
        ?Model $entidade = null,
        array $dados = []
    ): void {
        try {
            $request = request();

            self::create([
                'user_id' => auth()->id(),
                'modulo' => mb_strtoupper($modulo, 'UTF-8'),
                'acao' => mb_strtoupper($acao, 'UTF-8'),
                'descricao' => $descricao,
                'entidade_tipo' => $entidade ? get_class($entidade) : null,
                'entidade_id' => $entidade?->getKey(),
                'dados' => $dados ?: null,
                'ip' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        } catch (\Throwable) {
            // Auditoria nao deve interromper a operacao principal do sistema.
        }
    }
}
