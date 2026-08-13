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
        'costo_usd',
        'stock_actual',
        'controla_inventario',
        'unidad_medida',
        'impuesto_id',
        'fuente_tasa',
        'estado',
    ];

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

    public function getPrecioBaseAttribute(): float
    {
        $base = $this->presentaciones()->where('activa', true)->where('factor_conversion', 1)->first();

        return $base ? (float) $base->precio_usd : 0;
    }

    public function getPrecioBaseBsAttribute(): float
    {
        return round($this->precio_base * $this->obtenerTasa(), 2);
    }

    protected function casts(): array
    {
        return [
            'controla_inventario' => 'boolean',
            'costo_usd' => 'float',
            'stock_actual' => 'float',
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

    public function impuesto()
    {
        return $this->belongsTo(Impuesto::class);
    }

    public function presentaciones()
    {
        return $this->hasMany(ProductoPresentacion::class);
    }

    public function presentacionesActivas()
    {
        return $this->presentaciones()->where('activa', true);
    }
}
