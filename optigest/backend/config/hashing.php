<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cifrado de contrasenas
    |--------------------------------------------------------------------------
    | Todas las contrasenas del sistema (usuarios: administrador, tecnico,
    | cotizador) se cifran con bcrypt antes de guardarse en la base de datos.
    | Nunca se almacena texto plano. El "rounds" define el costo
    | computacional del hash (mayor = mas seguro pero mas lento).
    */

    'driver' => 'bcrypt',

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 12),
        'verify' => true,
    ],

    'argon' => [
        'memory' => 65536,
        'threads' => 1,
        'time' => 4,
        'verify' => true,
    ],

    'rehash_on_login' => true,

];
