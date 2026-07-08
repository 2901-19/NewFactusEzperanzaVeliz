<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Producto extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'nombre',
        'categoria_id',
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

    public function getImagenUrlAttribute(): ?string
    {
        return $this->imagen ? asset('storage/' . $this->imagen) : null;
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
