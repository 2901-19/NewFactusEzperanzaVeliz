<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TasaCambio extends Model
{
    protected $fillable = [
        'tipo',
        'moneda',
        'monto',
        'fecha',
    ];
}
