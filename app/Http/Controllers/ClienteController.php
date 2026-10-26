<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{   //metodo que lista todos os clientes
    public function index()
    {   //usa o Eloquent para buscar todos os registros da tabela clientes.
        //é com se fosse o comando sql "SELECT * FROM clientes" o Laravel abstrai isso
        $clientes = Cliente::all();

        //monta resposta JSON
        return response()->json(['clientes' => $clientes]);
    }

    //metodo de criacao
    public function store(Request $request)
    {   //validate: Laravel validando os dados que chegaram.
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'telefone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'observacoes' => ['nullable', 'string']
        ]);

        //Cliente::creat usa o Eloquent para fazer algo equivalente a um INSERT INTO clientes
        $cliente = Cliente::create($dados);

        return response()->json([
            'mensagem' => 'Cliente criado com sucesso.',
            'cliente' => $cliente,
        ], 201);//responde com 201 -> Created
    }
}
