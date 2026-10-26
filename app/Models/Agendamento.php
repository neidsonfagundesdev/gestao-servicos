<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agendamento extends Model
{
    protected $fillable = [
        'cliente_id',
        'servico_id',
        'user_id',
        'data',
        'hora',
        'status',
        'observacoes',
    ];

    /*
    belongsTo-> Isso significa que um agendamento pertence a:
    * um Cliente
    * um Servico
    * um User
        
    Então o relacionamento fica:
    - Cliente 1 ---- N Agendamentos
    - Servico 1 ---- N Agendamentos
    - User    1 ---- N Agendamentos    
    */

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }

    public function funcionario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
