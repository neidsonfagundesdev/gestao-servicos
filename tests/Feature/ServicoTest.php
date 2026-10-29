<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class ServicoTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_funcionario_nao_pode_criar_servico(): void
    {
        $usuario = User::factory()->create([
            'perfil' => 'funcionario',
        ]);

        Sanctum::actingAs($usuario);

        $response = $this->postJson('/api/servicos', [
            'nome' => 'Serviço Teste',
            'descricao' => 'Serviço criado para teste.',
            'duracao_minutos' => 60,
            'preco' => 100,
            'ativo' => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_pode_criar_servico(): void
    {
        $usuario = User::factory()->create([
            'perfil' => 'admin',
        ]);

        Sanctum::actingAs($usuario);

        $response = $this->postJson('/api/servicos', [
            'nome' => 'Serviço Teste',
            'descricao' => 'Serviço criado para teste por admin.',
            'duracao_minutos' => 60,
            'preco' => 100,
            'ativo' => true,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('servicos', [
            'nome' => 'Serviço Teste',
            'duracao_minutos' => 60,
        ]);
    }

    public function test_funcionario_nao_pode_atualizar_servico(): void
    {
        $usuario = User::factory()->create([
            'perfil' => 'funcionario',
        ]);

        Sanctum::actingAs($usuario);

        $servico = \App\Models\Servico::create([
            'nome' => 'Serviço Original',
            'descricao' => 'Descrição original.',
            'duracao_minutos' => 60,
            'preco' => 100,
            'ativo' => true,
        ]);

        $response = $this->patchJson(
            "/api/servicos/{$servico->id}",
            [
                'nome' => 'Serviço Alterado',
            ]
        );

        $response->assertStatus(403);
    }

    public function test_funcionario_nao_pode_excluir_servico(): void
    {
        $usuario = User::factory()->create([
            'perfil' => 'funcionario',
        ]);

        Sanctum::actingAs($usuario);

        $servico = \App\Models\Servico::create([
            'nome' => 'Serviço para exclusão',
            'descricao' => 'Serviço criado para teste de permissão.',
            'duracao_minutos' => 60,
            'preco' => 100,
            'ativo' => true,
        ]);

        $response = $this->deleteJson(
            "/api/servicos/{$servico->id}"
        );

        $response->assertStatus(403);
    }

    public function test_admin_pode_excluir_servico(): void
    {
        $usuario = User::factory()->create([
            'perfil' => 'admin',
        ]);

        Sanctum::actingAs($usuario);

        $servico = \App\Models\Servico::create([
            'nome' => 'Serviço para exclusão',
            'descricao' => 'Serviço criado para teste de exclusão por admin.',
            'duracao_minutos' => 60,
            'preco' => 100,
            'ativo' => true,
        ]);

        $response = $this->deleteJson(
            "/api/servicos/{$servico->id}"
        );

        $response->assertStatus(204);

        $this->assertDatabaseMissing('servicos', [
            'id' => $servico->id,
        ]);
    }
}

