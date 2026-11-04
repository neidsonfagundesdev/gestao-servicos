<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Cliente;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class ClienteTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_funcionario_nao_pode_excluir_cliente(): void
    {
        $usuario = User::factory()->create([
            'perfil' => 'funcionario',
        ]);

        Sanctum::actingAs($usuario);

        $cliente = Cliente::create([
            'nome' => 'Cliente Teste',
            'telefone' => '71999999999',
            'email' => 'cliente@teste.com',
            'observacoes' => 'Cliente para teste de permissão.',
        ]);

        $response = $this->deleteJson(
            "/api/clientes/{$cliente->id}"
        );

        $response->assertStatus(403);
    }

    public function test_admin_pode_excluir_cliente(): void
    {
        $usuario = User::factory()->create([
            'perfil' => 'admin',
        ]);

        Sanctum::actingAs($usuario);

        $cliente = Cliente::create([
            'nome' => 'Cliente Teste',
            'telefone' => '71999999999',
            'email' => 'cliente2@teste.com',
            'observacoes' => 'Cliente para teste de exclusão por admin.',
        ]);

        $response = $this->deleteJson(
            "/api/clientes/{$cliente->id}"
        );

        $response->assertStatus(204);

        $this->assertDatabaseMissing('clientes', [
            'id' => $cliente->id,
        ]);
    }

    public function test_nao_pode_excluir_cliente_com_agendamento(): void
    {
        $usuario = User::factory()->create([
            'perfil' => 'admin',
        ]);

        Sanctum::actingAs($usuario);

        $cliente = Cliente::create([
            'nome' => 'Cliente com agendamento',
            'telefone' => '71999999999',
            'email' => 'cliente.agendamento@teste.com',
        ]);

        $servico = \App\Models\Servico::create([
            'nome' => 'Serviço Teste',
            'duracao_minutos' => 60,
            'preco' => 100,
            'ativo' => true,
        ]);

        \App\Models\Agendamento::create([
            'cliente_id' => $cliente->id,
            'servico_id' => $servico->id,
            'user_id' => $usuario->id,
            'data' => now()->addDay()->format('Y-m-d'),
            'hora' => '10:00',
            'status' => 'agendado',
        ]);

        $response = $this->deleteJson(
            "/api/clientes/{$cliente->id}"
        );

        $response->assertStatus(409);

        $this->assertDatabaseHas('clientes', [
            'id' => $cliente->id,
        ]);
    }
}

