<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {

        //registra o middleware admin
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        // Para rotas de API, evita tentar redirecionar
        // usuários não autenticados para uma rota chamada "login".
        $middleware->redirectGuestsTo(function (Request $request) {

            if ($request->is('api/*')) {
                return null;
            }

            return '/login';
        });
    })

    ->withExceptions(function (Exceptions $exceptions): void {

        // Faz todas as rotas /api/* retornarem erros em JSON.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) =>
                $request->is('api/*') || $request->expectsJson()
        );

        // Quando o usuário não estiver autenticado,
        // retornamos 401 em JSON.
        $exceptions->render(function (
            AuthenticationException $e,
            Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'erro' => 'Não autenticado.'
                ], 401);
            }
        });
    })

    ->create();