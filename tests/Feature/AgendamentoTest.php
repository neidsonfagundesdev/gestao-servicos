<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use App\Models\Cliente;
use App\Models\Servico;
use App\Models\Agendamento;


class AgendamentoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_usuario_nao_autenticado_nao_pode_listar_agendamentos(): void
    {
        $response = $this->getJson('/api/agendamentos');

        $response->assertStatus(401);
    }

    public function test_usuario_autenticado_pode_listar_agendamentos(): void
    {
        $usuario = User::factory()->create();

        Sanctum::actingAs($usuario);

        $response = $this->getJson('/api/agendamentos');

        $response->assertStatus(200);
    }

    public function test_nao_pode_criar_agendamento_em_data_passada(): void
    {
        $usuario = User::factory()->create();

        Sanctum::actingAs($usuario);

        $cliente = Cliente::create([
            'nome' => 'Cliente Teste',
            'telefone' => '71999999999',
            'email' => 'cliente@teste.com',
            'observacoes' => 'Cliente criado para teste automatizado.',
        ]);

        $servico = Servico::create([
            'nome' => 'Serviço Teste',
            'descricao' => 'Serviço criado para teste automatizado.',
            'duracao_minutos' => 60,
            'preco' => 100,
            'ativo' => true,
        ]);

        $response = $this->postJson('/api/agendamentos', [
            'cliente_id' => $cliente->id,
            'servico_id' => $servico->id,
            'data' => now()->subDay()->format('Y-m-d'),
            'hora' => '10:00',
            'observacoes' => 'Teste com data passada.',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'data'
        ]);
    }

    public function test_nao_pode_criar_agendamento_com_servico_inativo(): void
    {
        $usuario = User::factory()->create();

        Sanctum::actingAs($usuario);

        $cliente = Cliente::create([
            'nome' => 'Cliente Teste',
            'telefone' => '71999999999',
            'email' => 'cliente2@teste.com',
            'observacoes' => 'Cliente para teste de serviço inativo.',
        ]);

        $servico = Servico::create([
            'nome' => 'Serviço Inativo',
            'descricao' => 'Serviço criado para teste automatizado.',
            'duracao_minutos' => 60,
            'preco' => 100,
            'ativo' => false,
        ]);

        $response = $this->postJson('/api/agendamentos', [
            'cliente_id' => $cliente->id,
            'servico_id' => $servico->id,
            'data' => now()->addDay()->format('Y-m-d'),
            'hora' => '10:00',
            'observacoes' => 'Teste com serviço inativo.',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'servico_id'
        ]);
    }

    public function test_nao_pode_criar_agendamento_com_conflito_de_horario(): void
    {
        $usuario = User::factory()->create();

        Sanctum::actingAs($usuario);

        $cliente = Cliente::create([
            'nome' => 'Cliente Teste',
            'telefone' => '71999999999',
            'email' => 'cliente3@teste.com',
            'observacoes' => 'Cliente para teste de conflito.',
        ]);

        $servico = Servico::create([
            'nome' => 'Serviço de 90 minutos',
            'descricao' => 'Serviço criado para testar conflito.',
            'duracao_minutos' => 90,
            'preco' => 150,
            'ativo' => true,
        ]);

        Agendamento::create([
            'cliente_id' => $cliente->id,
            'servico_id' => $servico->id,
            'user_id' => $usuario->id,
            'data' => now()->addDay()->format('Y-m-d'),
            'hora' => '10:00',
            'status' => 'agendado',
            'observacoes' => 'Agendamento existente.',
        ]);

        $response = $this->postJson('/api/agendamentos', [
            'cliente_id' => $cliente->id,
            'servico_id' => $servico->id,
            'data' => now()->addDay()->format('Y-m-d'),
            'hora' => '11:00',
            'observacoes' => 'Tentativa com conflito.',
        ]);

        $response->assertStatus(409);

        $response->assertJson([
            'erro' => 'Este funcionário já possui um agendamento neste período.'
        ]);
    }

    public function test_nao_pode_alterar_user_id_do_agendamento(): void
    {
        $usuario = User::factory()->create();

        Sanctum::actingAs($usuario);

        $cliente = Cliente::create([
            'nome' => 'Cliente Teste',
            'telefone' => '71999999999',
            'email' => 'cliente4@teste.com',
            'observacoes' => 'Cliente para teste de user_id proibido.',
        ]);

        $servico = Servico::create([
            'nome' => 'Serviço Teste',
            'descricao' => 'Serviço para teste.',
            'duracao_minutos' => 60,
            'preco' => 100,
            'ativo' => true,
        ]);

        $agendamento = Agendamento::create([
            'cliente_id' => $cliente->id,
            'servico_id' => $servico->id,
            'user_id' => $usuario->id,
            'data' => now()->addDay()->format('Y-m-d'),
            'hora' => '14:00',
            'status' => 'agendado',
            'observacoes' => 'Agendamento de teste.',
        ]);

        $response = $this->patchJson(
            "/api/agendamentos/{$agendamento->id}",
            [
                'user_id' => 999
            ]
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'user_id'
        ]);
    }

    public function test_funcionario_nao_pode_excluir_agendamento(): void
    {
        $usuario = User::factory()->create([
            'perfil' => 'funcionario',
        ]);

        Sanctum::actingAs($usuario);

        $cliente = Cliente::create([
            'nome' => 'Cliente Teste',
            'telefone' => '71999999999',
            'email' => 'cliente5@teste.com',
            'observacoes' => 'Cliente para teste de permissão.',
        ]);

        $servico = Servico::create([
            'nome' => 'Serviço Teste',
            'descricao' => 'Serviço para teste de permissão.',
            'duracao_minutos' => 60,
            'preco' => 100,
            'ativo' => true,
        ]);

        $agendamento = Agendamento::create([
            'cliente_id' => $cliente->id,
            'servico_id' => $servico->id,
            'user_id' => $usuario->id,
            'data' => now()->addDay()->format('Y-m-d'),
            'hora' => '16:00',
            'status' => 'agendado',
            'observacoes' => 'Agendamento para teste de permissão.',
        ]);

        $response = $this->deleteJson(
            "/api/agendamentos/{$agendamento->id}"
        );

        $response->assertStatus(403);
    }

    public function test_admin_pode_excluir_agendamento(): void
    {
        $usuario = User::factory()->create([
            'perfil' => 'admin',
        ]);

        Sanctum::actingAs($usuario);

        $cliente = Cliente::create([
            'nome' => 'Cliente Teste',
            'telefone' => '71999999999',
            'email' => 'cliente6@teste.com',
            'observacoes' => 'Cliente para teste de admin.',
        ]);

        $servico = Servico::create([
            'nome' => 'Serviço Teste',
            'descricao' => 'Serviço para teste de admin.',
            'duracao_minutos' => 60,
            'preco' => 100,
            'ativo' => true,
        ]);

        $agendamento = Agendamento::create([
            'cliente_id' => $cliente->id,
            'servico_id' => $servico->id,
            'user_id' => $usuario->id,
            'data' => now()->addDay()->format('Y-m-d'),
            'hora' => '17:00',
            'status' => 'agendado',
            'observacoes' => 'Agendamento para teste de exclusão por admin.',
        ]);

        $response = $this->deleteJson(
            "/api/agendamentos/{$agendamento->id}"
        );

        $response->assertStatus(204);

        $this->assertDatabaseMissing('agendamentos', [
            'id' => $agendamento->id,
        ]);
    }

    public function test_agendamento_cancelado_nao_bloqueia_horario(): void
    {
        $usuario = User::factory()->create();

        Sanctum::actingAs($usuario);

        $cliente = Cliente::create([
            'nome' => 'Cliente Teste',
            'telefone' => '71999999999',
        ]);

        $servico = Servico::create([
            'nome' => 'Serviço Teste',
            'duracao_minutos' => 60,
            'preco' => 100,
            'ativo' => true,
        ]);

        // Já existia um agendamento nesse horário,
        // mas ele foi cancelado.
        Agendamento::create([
            'cliente_id' => $cliente->id,
            'servico_id' => $servico->id,
            'user_id' => $usuario->id,
            'data' => now()->addDay()->format('Y-m-d'),
            'hora' => '10:00',
            'status' => 'cancelado',
        ]);

        // Tenta reutilizar exatamente o mesmo horário.
        $response = $this->postJson('/api/agendamentos', [
            'cliente_id' => $cliente->id,
            'servico_id' => $servico->id,
            'data' => now()->addDay()->format('Y-m-d'),
            'hora' => '10:00',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseCount('agendamentos', 2);
    }

    public function test_banco_nao_permite_dois_agendamentos_ativos_no_mesmo_horario(): void
    {
        $usuario = User::factory()->create();

        $cliente = Cliente::create([
            'nome' => 'Cliente Teste',
            'telefone' => '71999999999',
        ]);

        $servico = Servico::create([
            'nome' => 'Serviço Teste',
            'duracao_minutos' => 60,
            'preco' => 100,
            'ativo' => true,
        ]);

        Agendamento::create([
            'cliente_id' => $cliente->id,
            'servico_id' => $servico->id,
            'user_id' => $usuario->id,
            'data' => now()->addDay()->format('Y-m-d'),
            'hora' => '10:00',
            'status' => 'agendado',
        ]);

        $this->expectException(
            \Illuminate\Database\UniqueConstraintViolationException::class
        );

        // Mesmo horário e mesmo funcionário.
        // O status é diferente, mas ambos ocupam o horário.
        Agendamento::create([
            'cliente_id' => $cliente->id,
            'servico_id' => $servico->id,
            'user_id' => $usuario->id,
            'data' => now()->addDay()->format('Y-m-d'),
            'hora' => '10:00',
            'status' => 'confirmado',
        ]);
    }

    public function test_funcionario_nao_pode_editar_agendamento_de_outro_funcionario(): void
    {
        $funcionarioA = User::factory()->create([
            'perfil' => 'funcionario',
        ]);

        $funcionarioB = User::factory()->create([
            'perfil' => 'funcionario',
        ]);

        Sanctum::actingAs($funcionarioA);

        $cliente = Cliente::create([
            'nome' => 'Cliente Teste',
            'telefone' => '71999999999',
        ]);

        $servico = Servico::create([
            'nome' => 'Serviço Teste',
            'duracao_minutos' => 60,
            'preco' => 100,
            'ativo' => true,
        ]);

        $agendamento = Agendamento::create([
            'cliente_id' => $cliente->id,
            'servico_id' => $servico->id,
            'user_id' => $funcionarioB->id,
            'data' => now()->addDay()->format('Y-m-d'),
            'hora' => '10:00',
            'status' => 'agendado',
        ]);

        $response = $this->patchJson(
            "/api/agendamentos/{$agendamento->id}",
            [
                'status' => 'confirmado',
            ]
        );

        $response->assertStatus(403);

        $this->assertDatabaseHas('agendamentos', [
            'id' => $agendamento->id,
            'status' => 'agendado',
        ]);
    }

    public function test_admin_pode_editar_agendamento_de_outro_funcionario(): void
    {
        $admin = User::factory()->create([
            'perfil' => 'admin',
        ]);

        $funcionario = User::factory()->create([
            'perfil' => 'funcionario',
        ]);

        Sanctum::actingAs($admin);

        $cliente = Cliente::create([
            'nome' => 'Cliente Teste',
            'telefone' => '71999999999',
        ]);

        $servico = Servico::create([
            'nome' => 'Serviço Teste',
            'duracao_minutos' => 60,
            'preco' => 100,
            'ativo' => true,
        ]);

        $agendamento = Agendamento::create([
            'cliente_id' => $cliente->id,
            'servico_id' => $servico->id,
            'user_id' => $funcionario->id,
            'data' => now()->addDay()->format('Y-m-d'),
            'hora' => '11:00',
            'status' => 'agendado',
        ]);

        $response = $this->patchJson(
            "/api/agendamentos/{$agendamento->id}",
            [
                'status' => 'confirmado',
            ]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('agendamentos', [
            'id' => $agendamento->id,
            'status' => 'confirmado',
        ]);
    }

}


