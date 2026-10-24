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
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();//cria o id como chave primária e auto incremento.
            $table->string('nome', 150);//cria um VARCHAR(150) para o nome.
            $table->string('telefone', 20);//guarda telefone como texto, não como número.
            //O nullable() significa: esse campo pode receber NULL
            $table->string('email', 255)->nullable();//e-mail com até 255 caracteres e pode ficar vazio.
            $table->text('observacoes')->nullable();//campo maior para observações e também opcional.
            $table->timestamps();//cria created_at e updated_at.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
