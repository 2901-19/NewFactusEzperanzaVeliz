<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TasaCambio extends Model
{
    use HasFactory;
    protected $fillable = [
        'tipo',
        'nombre',
        'monto',
        'fecha',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public static function activaDe(string $tipo): ?TasaCambio
    {
        return static::where('tipo', $tipo)->activas()->latest('id')->first();
    }
}
