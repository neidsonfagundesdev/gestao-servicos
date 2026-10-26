<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/* O $fillable protege contra um ataque chamado "Mass Assignment" (Atribuição em Massa) */

class Servico extends Model
{
    protected $fillable = [
        'nome',
        'descricao',
        'duracao_minutos',
        'preco',
        'ativo',
    ];


    /*
    Agendamento belongsTo Servico
    Servico hasMany Agendamentos 
    Um servico possui varios agendamentos
    */
    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class);
    }
}
