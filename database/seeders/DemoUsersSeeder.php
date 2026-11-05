<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    /**
     * Cria os usuários iniciais do sistema.
     *
     * Usamos firstOrCreate porque:
     *
     * - se o usuário não existe, ele é criado;
     * - se já existe, não criamos outro;
     * - podemos executar o seeder várias vezes
     *   sem duplicar os usuários.
     */
    public function run(): void
    {
        /*
        As senhas não ficam escritas no código.

        Elas serão lidas das variáveis de ambiente
        configuradas no Render.
        */
        $senhaAdmin = config('demo.admin_password');
        $senhaFuncionario = config('demo.funcionario_password');

        /*
        Se as senhas não estiverem configuradas,
        não criamos os usuários.

        Isso evita criar conta com senha vazia.
        */
        if (!$senhaAdmin || !$senhaFuncionario) {
            $this->command?->warn(
                'Senhas dos usuários de demonstração não configuradas.'
            );

            return;
        }

        /*
        Cria o administrador somente
        se ele ainda não existir.
        */
        User::updateOrCreate(
            [
                'email' => 'admin@teste.com',
            ],
            [
                'name' => 'Administrador Teste',
                'password' => Hash::make($senhaAdmin),
                'perfil' => 'admin',
            ]
        );

        /*
        Cria o funcionário somente
        se ele ainda não existir.
        */
        User::updateOrCreate(
            [
                'email' => 'funcionario@teste.com',
            ],
            [
                'name' => 'Funcionário Teste',
                'password' => Hash::make($senhaFuncionario),
                'perfil' => 'funcionario',
            ]
        );
    }
}