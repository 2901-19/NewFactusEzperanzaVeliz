<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemFactura extends Model
{
    protected $table = 'items_factura';

    protected $fillable = [
        'factura_id',
        'producto_id',
        'presentacion_id',
        'presentacion_nombre',
        'factor_conversion',
        'cantidad',
        'precio_unitario_usd',
        'precio_unitario_bs',
        'subtotal',
        'unidad_medida',
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function presentacion()
    {
        return $this->belongsTo(ProductoPresentacion::class);
    }

    protected function casts(): array
    {
        return [
            'cantidad' => 'float',
            'factor_conversion' => 'float',
            'precio_unitario_usd' => 'float',
            'precio_unitario_bs' => 'float',
            'subtotal' => 'float',
        ];
    }
}
