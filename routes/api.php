<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\AgendamentoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

                              /* ROTAS CLIENTE */

// admin e funcionario autenticados
Route::middleware('auth:sanctum')->group(function () {

    //rota para solicitar a lista de clientes
    Route::get('/clientes', [ClienteController::class, 'index']);

    //rota para buscar um cliente especifico pelo ID
    Route::get('/clientes/{id}', [ClienteController::class, 'show']);

    //rota para criar
    Route::post('/clientes', [ClienteController::class, 'store']);

    //rota para atualizacao dos dados
    Route::patch('/clientes/{id}', [ClienteController::class, 'update']);

});

// somente admin -> rota para deletar os dados por ID
Route::middleware(['auth:sanctum', 'admin'])->delete(
    '/clientes/{id}',
    [ClienteController::class, 'destroy']
);

                              /* ROTAS SERVICOS */

//permite qualquer usuario autenticado. Somente leitura.
Route::middleware('auth:sanctum')->group(function () {
                              
    //rota para solicitar  alista de servicos.
    Route::get('/servicos', [ServicoController::class, 'index']);

    //rota que busca servico por ID
    Route::get('/servicos/{id}', [ServicoController::class, 'show']);

});

//protege as rotas com autenticacao admin
Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    //rota para criar os servicos
    Route::post('/servicos', [ServicoController::class, 'store']);

    //rota para criar o servico
    Route::patch('/servicos/{id}', [ServicoController::class, 'update']);

    //rota para deletar um servico
    Route::delete('/servicos/{id}', [ServicoController::class, 'destroy']);

});

                               /* ROTAS AGENDAMENTOS */

//as rotas dependem do Bearer Token
Route::middleware('auth:sanctum')->group(function () {

    //rota protegida para buscar cliente, servico e funcionario
    Route::get('/agendamentos', [AgendamentoController::class, 'index']);

    //rota protegida para buscar os agendamentos por ID
    Route::get('/agendamentos/{id}', [AgendamentoController::class, 'show']);

    //rota protegida para criar o agendamento
    Route::post('/agendamentos', [AgendamentoController::class, 'store']);

    //rota protegida para atualizar o agendamento pelo ID
    Route::patch('/agendamentos/{id}', [AgendamentoController::class, 'update']);

});

//rota para deletar agendamento -> somente admin
Route::middleware(['auth:sanctum', 'admin'])->delete(
    '/agendamentos/{id}',
    [AgendamentoController::class, 'destroy']
);

                               /* ROTAS LOGIN */

//rota para autenticacao de login e senha
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

// rotas protegidas por autenticação
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', function (Request $request) {
        return response()->json([
            'usuario' => $request->user()
        ], 200);
    });

    Route::post('/logout', [AuthController::class, 'logout']);
});

                               /* ROTA DASHBOARD */

Route::middleware('auth:sanctum')->get(
    '/dashboard',
    [DashboardController::class, 'index']
);