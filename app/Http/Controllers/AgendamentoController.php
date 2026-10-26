<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Agendamento;
use Illuminate\Database\UniqueConstraintViolationException;

class AgendamentoController extends Controller
{   
    //metodo que busca os agendamentos
    public function index()
    {   /*o with diz ao Eloquent: 
        além dos dados do agendamento, 
        traga também os dados relacionados de cliente, serviço e funcionário.*/
        $agendamentos = Agendamento::with([
            'cliente',
            'servico',
            'funcionario',
        ])->get();

        return response()->json(['agendamentos' => $agendamentos]);
    }

    //metodo para criar p agendamento
    public function store(Request $request)
    {   //exists:clientes,id -> garante que o cliente_id realmente exista na tabela clientes
        //mesma logica para servico_id -> servicos.id e user_id -> users_id
        $dados = $request->validate([
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'servico_id' => ['required', 'integer', 'exists:servicos,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'data' => ['required', 'date'],
            'hora' => ['required', 'date_format:H:i'],
            'status' => ['sometimes', 'in:agendado,confirmado,em_atendimento, concluido, cancealdo'],
            'observacoes' => ['nullable', 'string'],
        ]);

        /*
        Agendamento::where vai na tabela agendamentos
        traz somente os agendamentos desse funcionario-> user
        esse bloco funciona como barreira para impedir que sejam gerados agendamentos 
        na mesma data e horario 
        */
        $conflito = Agendamento::where('user_id', $dados['user_id'])
            //filtra apenas os que são na data e hora exata que o usuário está tentando marcar.
            ->where('data', $dados['data'])
            ->where('hora', $dados['hora'])
            ->exists();// O exists() só devolve TRUE (já tem) ou FALSE (está livre).

        if ($conflito) {
            return response()->json([
                'erro' => 'Este funcionário já possui um agendamento neste horário.'
            ], 409);
        }

        /*
        Este bloco faz com que mesmo com duas requisicoes simultanes passe pela barreira
        do $conflito o segundo INSERT ainda vai
        falhar no banco. Com o catch Devolvemos 409, não 500
        */
        try {

            $agendamento = Agendamento::create($dados);

        } catch (UniqueConstraintViolationException $e) {
            return response()->json([
                'erro' => 'Este funcionário já possui um agendamento neste horário.'
            ], 409);
        }
        
        //carrega os relacionamentos depois da criação, para a resposta voltar completa
        $agendamento->load([
            'cliente',
            'servico',
            'funcionario'
        ]);

        return response()->json([
            'mensagem' => 'Agendamento criado com sucesso',
            'agendamento' => $agendamento,
        ], 201);
    }

    //metodo para buscar agendamento por ID
    public function show(int $id)
    {   //busca o agendamento pelo id e já traz junto os dados relacionados
        $agendamento = Agendamento::with([
            'cliente',
            'servico',
            'funcionario'
        ])->find($id);

        if (!$agendamento) {
            return response()->json([
                'erro' => 'Agendamento não encontrado.'
            ], 404);
        }

        return response()->json([
            'agendamento' => $agendamento
        ]);
    }

    //metodo para atualizar os agendamentos
    public function update(Request $request, int $id)
    {   
        //Busca o agendamento no banco. Se não achar, avisa que não existe (404)
        $agendamento = Agendamento::find($id);

        if (!$agendamento) {
            return response()->json([
                'erro' => 'Agendamento não encontrado.'
            ], 404);
        }

        //Valida os dados que chegaram.
        // O 'sometimes' permite atualizar só uma parte (ex: só o status).
        // O 'exists' garante que os IDs de cliente, serviço e user realmente existem.
        $dados = $request->validate([
            'cliente_id' => ['sometimes', 'required', 'integer', 'exists:clientes,id'],
            'servico_id' => ['sometimes', 'required', 'integer', 'exists:servicos,id'],
            'user_id' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'data' => ['sometimes', 'required', 'date'],
            'hora' => ['sometimes', 'required', 'date_format:H:i'],
            'status' => ['sometimes', 'in:agendado,confirmado,em_atendimento,concluido,cancelado'],
            'observacoes' => ['sometimes', 'nullable', 'string'],
        ]);

        //Se o usuário não mandou nova data/hora/funcionário, usamos os que já estavam salvos
        $userId = $dados['user_id'] ?? $agendamento->user_id;
        $data = $dados['data'] ?? $agendamento->data;
        $hora = $dados['hora'] ?? $agendamento->hora;

        //Checa se a NOVA data/hora entra em conflito com outro agendamento do mesmo funcionário.
        // O 'id != agendamento->id' ignora o próprio agendamento que estamos editando.
        $conflito = Agendamento::where('user_id', $userId)
            ->where('data', $data)
            ->where('hora', $hora)
            ->where('id', '!=', $agendamento->id)
            ->exists();

        if ($conflito) {
            return response()->json([
                'erro' => 'Este funcionário já possui um agendamento neste horário.'
            ], 409);
        }

        //Tenta atualizar no banco. 
        //Se a regra 'Unique' do banco bloquear (dois cliques ao mesmo tempo), o catch pega o erro.
        try {

            $agendamento->update($dados);

        } catch (UniqueConstraintViolationException $e) {
            return response()->json([
                'erro' => 'Este funcionário já possui um agendamento neste horário.'
            ], 409);
        }

        //Recarrega os relacionamentos para devolver o JSON completo
        $agendamento->load([
            'cliente',
            'servico',
            'funcionario'
        ]);

        return response()->json([
            'mensagem' => 'Agendamento atualizado com sucesso.',
            'agendamento' => $agendamento
        ]);
    }

    public function destroy(int $id)
    {
        $agendamento = Agendamento::find($id);

        if (!$agendamento) {
            return response()->json([
                'erro' => 'Agendamento não encontrado.'
            ], 404);
        }

        $agendamento->delete();

        return response()->json(null, 204);
    }
}
