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
        'unidades_por_paquete',
        'stock_paquetes',
        'stock_unidades',
        'precio_unitario_usd',
        'precio_mayor_usd',
        'cantidad_minima_mayor',
        'tiene_iva',
        'fuente_tasa',
        'estado',
    ];

    public function getStockTotalAttribute(): int
    {
        return ($this->stock_paquetes * $this->unidades_por_paquete) + $this->stock_unidades;
    }

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
