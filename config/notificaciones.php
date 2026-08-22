<?php

use App\Services\Notificaciones\Tipos\RecordatorioTasa;

return [

    /*
    |--------------------------------------------------------------------------
    | Tipos de notificación
    |--------------------------------------------------------------------------
    | Cada clase implementa App\Services\Notificaciones\Contracts\Notificacion.
    | Para agregar una notificación nueva: crea la clase y añádela aquí.
    */

    'tipos' => [
        RecordatorioTasa::class,
    ],

];
