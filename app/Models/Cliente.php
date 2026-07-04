<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cliente extends Model
{
    use HasFactory;
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
