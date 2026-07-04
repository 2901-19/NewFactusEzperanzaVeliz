<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'subtotal_bs',
        'iva_bs',
        'total_bs',
        'total_usd',
        'estado',
        'estado_credito',
        'fecha_venta',
    ];

    protected function casts(): array
    {
        return [
            'productos' => 'array',
            'fecha_venta' => 'datetime',
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
