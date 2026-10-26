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
}
