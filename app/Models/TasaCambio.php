<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TasaCambio extends Model
{
    use HasFactory;
    protected $fillable = [
        'tipo',
        'moneda',
        'monto',
        'fecha',
    ];
}
