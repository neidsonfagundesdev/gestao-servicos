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
    /*
    O request aqui é o "entregador". 
    Ele pega tudo o que chegou na requisição 
    (JSON, formulário, arquivos) e traz para dentro do seu Controller.
    */
    public function store(Request $request)
    {   //validate: Laravel validando os dados que chegaram.
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'telefone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'observacoes' => ['nullable', 'string']
        ]);

        //Cliente::creat usa o Eloquent para fazer algo equivalente a um INSERT INTO clientes
        //Esses dados já foram validados pela funcao validate anterior a essa
        $cliente = Cliente::create($dados);

        return response()->json([
            'mensagem' => 'Cliente criado com sucesso.',
            'cliente' => $cliente,
        ], 201);//responde com 201 -> Created
    }

    public function show(int $id)
    {   //Cliente::find faz o Eloquent procurar pela chave primária id
        //é como fazer SELECT * FROM clientes WHERE id = ?
        $cliente = Cliente::find($id);

        //!$cliente a ! significa -> se não encontrar
        if (!$cliente) {
            return response()->json([
                'erro' => 'Cliente não encontrado.'
            ], 404);//retorna 404
        }

        return response()->json([
            'cliente' => $cliente
        ]);
    }

    public function update(Request $request, $id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json([
                'erro' => 'Cliente não encontrado.'
            ], 404);
        }

        /*sometimes quer dizer -> valide esse campo somente se ele vier na requisição.
        e ele não vier, o Laravel ignora.*/

        //neste caso temos possibilidade de atualizar tudo ou somente um campo ex.: telefone e etc
        
        /*
        validate é uma validação de regras de negócio. 
        Ele confere se os dados estão no formato certo 
        (ex: se o e-mail tem @, se o nome está vazio).
        */ 
        $dados = $request->validate([
            'nome' => ['sometimes', 'required', 'string', 'max:150'],
            'telefone' => ['sometimes', 'required', 'string', 'max:20'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'observacoes' => ['sometimes', 'nullable', 'string'],
        ]);

        //funcao que atualiza os dados no banco
        $cliente->update($dados);

        return response()->json([
            'mensagem' => 'Cliente atualizado com sucesso.',
            'cliente' => $cliente,
        ]);
    }

    //metodo para excluir
    public function destroy($id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json([
                'erro' => 'Cliente não encontrado.'
            ], 404);
        }

        $cliente->delete();

        //não devolve nada, apenas status 204
        return response()->json(null, 204);
    }

}
