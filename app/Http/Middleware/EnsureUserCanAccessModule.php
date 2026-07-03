<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessModule
{
    public function handle(Request $request, Closure $next, string $modulo): Response
    {
        $user = $request->user();

        if (!$user || !$user->ativo || !$user->podeAcessar($modulo)) {
            abort(403, 'Acesso não autorizado.');
        }

        return $next($request);
    }
}
