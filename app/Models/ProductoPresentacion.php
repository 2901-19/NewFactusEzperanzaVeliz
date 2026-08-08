<?php

namespace App\Models;

use App\Services\TasaCambioService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoPresentacion extends Model
{
    use HasFactory;

    protected $table = 'producto_presentaciones';

    protected $fillable = [
        'producto_id',
        'nombre',
        'factor_conversion',
        'margen',
        'precio_usd',
        'activa',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function getPrecioBsAttribute(): float
    {
        return round((float) $this->precio_usd * $this->obtenerTasa(), 2);
    }

    private function obtenerTasa(): float
    {
        return $this->producto?->fuente_tasa
            ? (TasaCambioService::monto($this->producto->fuente_tasa) ?? 1)
            : 1;
    }

    protected function casts(): array
    {
        return [
            'factor_conversion' => 'float',
            'margen' => 'float',
            'precio_usd' => 'float',
            'activa' => 'boolean',
        ];
    }
}
