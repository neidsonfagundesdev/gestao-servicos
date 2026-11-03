<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Executa os seeders do sistema.
     */
    public function run(): void
    {
        /*
        Cria os usuários iniciais.
        */
        $this->call([
            DemoUsersSeeder::class,
        ]);
    }
}