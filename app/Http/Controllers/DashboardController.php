<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Servico;

class DashboardController extends Controller
{
    public function index()
    {
        /*
        Conta quantos clientes existem no sistema.
        */
        $totalClientes = Cliente::count();

        /*
        Conta somente os serviços ativos.

        ativo = 1
        */
        $servicosAtivos = Servico::where('ativo', true)->count();

        /*
        Conta todos os agendamentos cadastrados.
        */
        $totalAgendamentos = Agendamento::count();

        /*
        Conta os agendamentos de hoje.

        whereDate compara somente a data,
        ignorando horário.
        */
        $agendamentosHoje = Agendamento::whereDate(
            'data',
            today()
        )->count();

        /*
        Busca os próximos agendamentos.

        - somente de hoje para frente
        - ordena por data
        - depois por hora
        - traz cliente, serviço e funcionário
        - limita aos próximos 5
        */
        $proximosAgendamentos = Agendamento::with([
            'cliente',
            'servico',
            'funcionario'
        ])
            ->whereDate('data', '>=', today())
            ->orderBy('data')
            ->orderBy('hora')
            ->limit(5)
            ->get();

        /*
        Retorna o resumo para o React.
        */
        return response()->json([
            'total_clientes' => $totalClientes,
            'servicos_ativos' => $servicosAtivos,
            'total_agendamentos' => $totalAgendamentos,
            'agendamentos_hoje' => $agendamentosHoje,
            'proximos_agendamentos' => $proximosAgendamentos,
        ], 200);
    }
}