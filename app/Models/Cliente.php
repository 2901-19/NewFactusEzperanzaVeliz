<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'ci',
        'nombre',
        'telefono',
    ];

    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }
}
