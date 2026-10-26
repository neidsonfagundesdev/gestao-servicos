<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;

//rota para solicitar a lista de clientes
Route::get('/clientes', [ClienteController::class, 'index']);

//rota para criar
Route::post('/clientes', [ClienteController::class, 'store']);