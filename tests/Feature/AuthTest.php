<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * A basic feature test example.
     */
    public function test_login_com_credenciais_validas_retorna_200(): void
    {
        $usuario = User::factory()->create([
            'email' => 'teste@email.com',
            'password' => '12345678',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'teste@email.com',
            'password' => '12345678',
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'mensagem',
            'usuario',
            'token',
        ]);
    }

    public function test_login_com_senha_incorreta_retorna_401(): void
    {
        User::factory()->create([
            'email' => 'teste@email.com',
            'password' => '12345678',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'teste@email.com',
            'password' => 'senha-errada',
        ]);

        $response->assertStatus(401);

        $response->assertJson([
            'erro' => 'E-mail ou senha inválidos.'
        ]);
    }

}
