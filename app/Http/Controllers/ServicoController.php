<?php

namespace App\Http\Controllers;

use App\Models\Servico;
use Illuminate\Http\Request;

class ServicoController extends Controller
{   //metodo que lista os servicos
    public function index()
    {
        $servicos = Servico::all();

        return response()->json([
            'servicos' => $servicos
        ]);
    }

    //metodo que cria o servio
    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'descricao' => ['nullable', 'string'],
            'duracao_minutos' => ['required', 'integer', 'min:1'],
            'preco' => ['required', 'numeric', 'min:0'],
            'ativo' => ['sometimes', 'boolean'],
        ]);

        $servico = Servico::create($dados);

        return response()->json([
            'mensagem' => 'Serviço criado com sucesso.',
            'servico' => $servico,
        ], 201);
    }

    //metodo que exibe os servicos por ID
    public function show(int $id)
    {
        $servico = Servico::find($id);

        if (!$servico) {
            return response()->json([
                'erro' => 'Serviço não encontrado.'
            ], 404);
        }

        return response()->json([
            'servico' => $servico
        ]);
    }

    //metodo para atualizar o servico por id
    public function update(Request $request, $id)
    {
        $servico = Servico::find($id);

        if (!$servico) {
            return response()->json([
                'erro' => 'Serviço não encontrado.'
            ], 404);
        }

        $dados = $request->validate([
            'nome' => ['sometimes', 'required', 'string', 'max:150'],
            'descricao' => ['sometimes', 'nullable', 'string'],
            'duracao_minutos' => ['sometimes', 'required', 'integer', 'min:1'],
            'preco' => ['sometimes', 'required', 'numeric', 'min:0'],
            'ativo' => ['sometimes', 'required', 'boolean'],
        ]);

        $servico->update($dados);

        return response()->json([
            'mensagem' => 'Serviço atualizado com sucesso.',
            'servico' => $servico
        ]);
    }

    //metodo para deletar um servico
    public function destroy($id)
    {
        $servico = Servico::find($id);

        if (!$servico) {
            return response()->json([
                'erro' => 'Serviço não encontrado.'
            ], 404);
        }

        // Verifica se esse serviço possui algum agendamento.
        if ($servico->agendamentos()->exists()) {
            return response()->json([
                'erro' => 'Não é possível excluir um serviço que possui agendamentos.'
            ], 409);
        }

        $servico->delete();

        return response()->json(null, 204);
    }

}
