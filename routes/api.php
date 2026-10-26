<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\AgendamentoController;

                              /* ROTAS CLIENTE */

//rota para solicitar a lista de clientes
Route::get('/clientes', [ClienteController::class, 'index']);

//rota para criar
Route::post('/clientes', [ClienteController::class, 'store']);

//rota para buscar um cliente especifico pelo ID
Route::get('/clientes/{id}', [ClienteController::class, 'show']);

//rota para atualizacao dos dados
Route::patch('/clientes/{id}', [ClienteController::class, 'update']);

//rota para deletar os dados por ID
Route::delete('/clientes/{id}', [ClienteController::class, 'destroy']);


                              /* ROTAS SERVICOS */

//rota para solicitar  alista de servicos.
Route::get('/servicos', [ServicoController::class, 'index']);

//rota para criar os servicos
Route::post('/servicos', [ServicoController::class, 'store']);

//rota que busca servico por ID
Route::get('/servicos/{id}', [ServicoController::class, 'show']);

//rota para cria o servico
Route::patch('/servicos/{id}', [ServicoController::class, 'update']);

//rota para deletar um servico
Route::delete('/servicos/{id}', [ServicoController::class, 'destroy']);

                               /* ROTAS AGENDAMENTOS */

//rota para buscar cliente, servico e funcionario
Route::get('/agendamentos', [AgendamentoController::class, 'index']);

//rota para criar o agendamento
Route::post('/agendamentos', [AgendamentoController::class, 'store']);

//rota para buscar os agendamentos por ID
Route::get('/agendamentos/{id}', [AgendamentoController::class, 'show']);

//rota para atualizar o agendamento pelo ID
Route::patch('/agendamentos/{id}', [AgendamentoController::class, 'update']);

//rota para deletar pelo ID
Route::delete('/agendamentos/{id}', [AgendamentoController::class, 'destroy']);