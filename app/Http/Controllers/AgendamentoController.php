<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Agendamento;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\Rule;
use App\Models\Servico;
use Carbon\Carbon;

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
            //servico id preciso existir e estar ativo o Rule::exists verifica isso
            'servico_id' => ['required', 'integer', Rule::exists('servicos', 'id')->where('ativo', true)],
            //after_or_equal:today -> a data precisa ser hoje ou furtura
            'data' => ['required', 'date', 'after_or_equal:today'],
            'hora' => ['required', 'date_format:H:i'],
            'status' => ['sometimes','in:agendado,confirmado,em_atendimento,concluido,cancelado'],
            'observacoes' => ['nullable', 'string'],
        ]);
        
        //Cria o post já vinculado ao usuário logado (injeta o user_id automaticamente).
        $dados['user_id'] = $request->user()->id;

        //Calcula o intervalo [início, fim] do novo agendamento
        //para depois verificar conflitos de horário com outros agendamentos
        $servico = Servico::find($dados['servico_id']);

        $inicioNovo = Carbon::parse($dados['data'] . ' ' . $dados['hora']);

        $fimNovo = $inicioNovo->copy()->addMinutes($servico->duracao_minutos);

        //Carrega os agendamentos do dia para verificar conflito de horário
        $agendamentosDoDia = Agendamento::with('servico')
            ->where('user_id', $dados['user_id'])
            ->where('data', $dados['data'])
            ->where('status', '!=', 'cancelado')
            ->get();

        // Dois intervalos se sobrepõem quando:
        // novo.início < existente.fim  &&  novo.fim > existente.início
        foreach ($agendamentosDoDia as $existente) {

            $inicioExistente = Carbon::parse(
                $existente->data . ' ' . $existente->hora
            );

            $fimExistente = $inicioExistente
                ->copy()
                ->addMinutes($existente->servico->duracao_minutos);

            $temConflito =
                $inicioNovo < $fimExistente &&
                $fimNovo > $inicioExistente;

            if ($temConflito) {
                return response()->json([
                    'erro' => 'Este funcionário já possui um agendamento neste período.'
                ], 409);
            }
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
            'servico_id' => ['sometimes', 'required', 'integer', Rule::exists('servicos', 'id')->where('ativo', true)],
            'user_id' => ['prohibited'],
            //after_or_equal:today -> a data precisa ser hoje ou furtura
            'data' => ['sometimes', 'required', 'date', 'after_or_equal:today'],
            'hora' => ['sometimes', 'required', 'date_format:H:i'],
            'status' => ['sometimes','in:agendado,confirmado,em_atendimento,concluido,cancelado'],
            'observacoes' => ['sometimes', 'nullable', 'string'],
        ]);

        //Se o usuário não mandou nova data/hora/funcionário, usamos os que já estavam salvos
        $userId = $agendamento->user_id;
        $data = $dados['data'] ?? $agendamento->data;
        $hora = $dados['hora'] ?? $agendamento->hora;

        //Usa o serviço informado ou mantém o atual (update parcial) e calcula o novo intervalo
        $servicoId = $dados['servico_id'] ?? $agendamento->servico_id;

        $servico = Servico::find($servicoId);

        $inicioNovo = Carbon::parse($data . ' ' . $hora);

        $fimNovo = $inicioNovo
            ->copy()
            ->addMinutes($servico->duracao_minutos);

        /*
        Carrega os agendamentos do dia para validar conflito no update.
        O id != $agendamento->id é essencial: sem ele, o próprio registro 
        seria considerado conflitante e qualquer edição seria bloqueada.
        */
        $agendamentosDoDia = Agendamento::with('servico')
            ->where('user_id', $userId)
            ->where('data', $data)
            ->where('status', '!=', 'cancelado')
            ->where('id', '!=', $agendamento->id)//exclui o agendamento que esta sendo editado
            ->get();

        //Verifica conflito com os outros agendamentos do dia (o próprio já foi excluído da busca)
        foreach ($agendamentosDoDia as $existente) {

            $inicioExistente = Carbon::parse(
                $existente->data . ' ' . $existente->hora
            );

            $fimExistente = $inicioExistente
                ->copy()
                ->addMinutes($existente->servico->duracao_minutos);

            $temConflito =
                $inicioNovo < $fimExistente &&
                $fimNovo > $inicioExistente;

            if ($temConflito) {
                return response()->json([
                    'erro' => 'Este funcionário já possui um agendamento neste período.'
                ], 409);
            }
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
