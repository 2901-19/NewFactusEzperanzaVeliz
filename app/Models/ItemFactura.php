<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemFactura extends Model
{
    protected $table = 'items_factura';

    protected $fillable = [
        'factura_id',
        'producto_id',
        'cantidad',
        'precio_unitario_usd',
        'precio_unitario_bs',
        'subtotal',
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
