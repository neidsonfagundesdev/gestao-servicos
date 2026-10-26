<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;

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