<?php

return [

    /*
    Senha do administrador.

    O valor vem da variável de ambiente:
    DEMO_ADMIN_PASSWORD
    */
    'admin_password' => env(
        'DEMO_ADMIN_PASSWORD'
    ),

    /*
    Senha do funcionário.

    O valor vem da variável:
    DEMO_FUNCIONARIO_PASSWORD
    */
    'funcionario_password' => env(
        'DEMO_FUNCIONARIO_PASSWORD'
    ),

];