<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre',
        'descripcion',
        'imagen',
        'stock',
        'paquetes',
        'unidades_paquete',
        'precio_costo_usd',
        'porcentaje_ganancia',
        'precio_venta_usd',
        'tiene_iva',
        'estado',
        'fuente_tasa',
    ];

    protected function casts(): array
    {
        return [
            'tiene_iva' => 'boolean',
        ];
    }

    public function items()
    {
        return $this->hasMany(ItemFactura::class);
    }
}
