<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/* O $fillable protege contra um ataque chamado "Mass Assignment" (Atribuição em Massa) */

class Cliente extends Model
{   //O $fillable é uma proteção do Laravel. 
    //Ele define quais campos podem ser preenchidos por operações
    protected $fillable = [
        'nome',
        'telefone',
        'email',
        'observacoes'
    ];
}
