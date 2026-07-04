<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Impuesto extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'porcentaje',
        'fecha',
    ];
}
