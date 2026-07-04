<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuraciones';

    protected $fillable = [
        'clave',
        'valor',
    ];

    public static function obtener(string $clave, string $defecto = ''): string
    {
        $config = self::where('clave', $clave)->first();
        return $config ? $config->valor : $defecto;
    }
}
