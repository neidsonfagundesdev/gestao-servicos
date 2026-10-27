<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{   
    /*
    $next -> a próxima camada da pipeline (outro middleware ou o controller). 
    Bloqueia o acesso a rotas que exigem perfil 'admin'.
    */
    public function handle(Request $request, Closure $next): Response
    {   
        //duas condicoes unidas. Não é autenticado, autenticado mas não é admin
        //retorna 403 -> você está autenticado, mas não tem permissão
        if (!$request->user() || $request->user()->perfil !== 'admin') {
            return response()->json([
                'erro' => 'Acesso permitido apenas para administradores.'
            ], 403);
        }

        //Chamar $next($request) significa "pode seguir".
        return $next($request);
    }
}