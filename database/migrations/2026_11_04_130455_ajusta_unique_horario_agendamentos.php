<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cria uma coluna calculada automaticamente.
        //
        // cancelado -> NULL
        // qualquer outro status -> 1
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->unsignedTinyInteger('bloqueia_horario')
                ->nullable()
                ->storedAs(
                    "CASE WHEN status = 'cancelado' THEN NULL ELSE 1 END"
                );
        });

        // Primeiro criamos o novo índice.
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->unique(
                ['user_id', 'data', 'hora', 'bloqueia_horario'],
                'agendamentos_horario_ativo_unique'
            );
        });

        // Depois podemos remover o índice antigo.
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->dropUnique(
                'agendamentos_user_data_hora_unique'
            );
        });
    }

    public function down(): void
    {
        // Restaura primeiro a regra antiga.
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->unique(
                ['user_id', 'data', 'hora'],
                'agendamentos_user_data_hora_unique'
            );
        });

        // Remove a regra nova.
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->dropUnique(
                'agendamentos_horario_ativo_unique'
            );
        });

        // Remove a coluna calculada.
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->dropColumn('bloqueia_horario');
        });
    }
    
};