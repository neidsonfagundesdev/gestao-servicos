<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {   //Altera a tabela 'agendamentos' que já existe
        Schema::table('agendamentos', function (Blueprint $table) {
            //Cria a regra de exclusividade (Unique Constraint).
            //uma regra no banco: a junção dessas 3 colunas NÃO pode se repetir.
            // Isso impede que duas pessoas marquem o mesmo funcionário no mesmo horário.
            $table->unique(
                ['user_id', 'data', 'hora'],
                // Damos um nome para essa regra (para conseguir apagar no down)
                'agendamentos_user_data_hora_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {   //desfaz a regra    
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->dropUnique('agendamentos_user_data_hora_unique');
        });
    }
};
