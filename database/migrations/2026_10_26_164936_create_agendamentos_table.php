<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    $table->foreignId('cliente_id')->constrained('clientes');
    o Laravel transforma isso em algo equivalente a:
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
    
    Isso cria a coluna: cliente_id 
    e também cria a chave estrangeira apontando para: clientes.id
    Mesma coisa com servico_id → 'servicos.id' e user_id → 'users.id'
    */
    public function up(): void
    {
        Schema::create('agendamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');//qual cliente marcou
            $table->foreignId('servico_id')->constrained('servicos');//qual serviço será realizado
            $table->foreignId('user_id')->constrained('users');//qual funcionário atenderá
            $table->date('data');//dia do agendamento
            $table->time('hora');//horário
            $table->string('status', 30)->default('agendado');//agendado, confirmado, em_atendimento, concluido ou cancelado
            $table->text('observacoes')->nullable();//campo opcional
            $table->timestamps();//cria created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agendamentos');
    }
};
