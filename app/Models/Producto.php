<?php

namespace App\Models;

use App\Services\TasaCambioService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'categoria_id',
        'descripcion',
        'imagen',
        'unidades_por_paquete',
        'stock_paquetes',
        'stock_unidades',
        'costo_usd',
        'margen_detal',
        'margen_mayor',
        'precio_unitario_usd',
        'precio_mayor_usd',
        'tiene_iva',
        'fuente_tasa',
        'estado',
    ];

    public function getStockTotalAttribute(): int
    {
        return ($this->stock_paquetes * $this->unidades_por_paquete) + $this->stock_unidades;
    }

    public function getCostoBsAttribute(): float
    {
        return $this->costo_usd * $this->obtenerTasa();
    }

    private function obtenerTasa(): float
    {
        return TasaCambioService::monto($this->fuente_tasa) ?? 1;
    }

    public function getImagenUrlAttribute(): ?string
    {
        return $this->imagen ? asset('storage/'.$this->imagen) : null;
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

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
}
