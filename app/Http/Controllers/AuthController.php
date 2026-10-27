<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{   
    //metodo para login
    public function login(Request $request)
    {   
        /*
        O Laravel já interrompe a requisição e 
        devolve um HTTP 422 automaticamente se os dados não passarem.
        */
        $dados = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        /*
        Pega o primeiro usuário com aquele e-mail. 
        Como o e-mail é único na tabela users, isso é seguro.
        */
        $usuario = User::where('email', $dados['email'])->first();

        /*
        Esse boco verifica as credenciais
        !$usuario quer dizer: email não existe no banco
        Hash::check é o método para comparar senha em texto puro 
        com o hash salvo no banco (bcrypt/argon). 
        */
        if (!$usuario || !Hash::check($dados['password'], $usuario->password)) {
            return repsonse()->json([
                'erro' => 'E-mail ou senha inválidos.'
            ], 401);
        }

        /*
        cria um Personal Access Token do Sanctum e salva o 
        hash dele no banco (tabela personal_access_tokens)
        'api-token' é só um rótulo do dispositivo/cliente 
        poderia ser 'web', 'android', 'ios'
        */
        $token = $usuario->createToken('api-token')->plainTextToken;

        return response()->json([
            'mensagem' => 'login realizado com sucesso.',
            'usuario' => $usuario,
            'token' => $token,
        ], 200);
    }

    //este bloco com o metodo logout Encerra a sessão atual do usuário 
    public function logout(Request $request)
    {   
        /*
        $request->user() -> Resolve o usuário autenticado da requisição.
        ->currentAccessToken() -> Retorna o token específico que 
        foi usado para autenticar esta requisição
        ->delete() -> Deleta a linha do token no banco de dados.
        */
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'mensagem' => 'Logout realizado com sucesso.'
        ], 200);
    }

}
