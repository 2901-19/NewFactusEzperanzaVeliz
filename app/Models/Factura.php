<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;

    protected $fillable = [
        'correlativo',
        'cliente_id',
        'user_id',
        'productos',
        'tasa_cambio',
        'metodo_pago',
        'detalle_pago',
        'subtotal_bs',
        'iva_bs',
        'total_bs',
        'total_usd',
        'pago_bs',
        'fecha_pago',
        'estado',
        'estado_credito',
        'fecha_venta',
    ];

    protected function casts(): array
    {
        return [
            'productos' => 'array',
            'detalle_pago' => 'array',
            'fecha_venta' => 'datetime',
            'fecha_pago' => 'datetime',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function items()
    {
        return $this->hasMany(ItemFactura::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
