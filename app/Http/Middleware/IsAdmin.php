<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware que verifica se o usuário autenticado é admin.
 *
 * Se o usuário não for admin, redireciona para o painel ADM com mensagem de erro.
 */
class IsAdmin
{
    /**
     * Processa a requisição entrante.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login')->with('error', 'Você precisa estar autenticado para acessar esta página.');
        }

        if (! auth()->user()->is_admin) {
            return redirect()->route('adm')->with('error', 'Apenas administradores podem acessar esta página.');
        }

        return $next($request);
    }
}
