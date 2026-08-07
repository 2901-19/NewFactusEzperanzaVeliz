<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class TasaCambio extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo',
        'nombre',
        'monto',
        'fecha',
        'activo',
        'user_id',
        'origen',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function ultimaDe(string $tipo): ?TasaCambio
    {
        return static::where('tipo', $tipo)->latest('id')->first();
    }

    public static function activaDe(string $tipo): ?TasaCambio
    {
        $vigente = static::ultimaDe($tipo);

        return $vigente && $vigente->activo ? $vigente : null;
    }

    public static function ultimasPorTipo(): Collection
    {
        $ultimosIds = static::query()
            ->selectRaw('MAX(id) as id')
            ->groupBy('tipo')
            ->pluck('id');

        return static::query()
            ->whereIn('id', $ultimosIds)
            ->orderBy('tipo')
            ->get()
            ->keyBy('tipo');
    }

    public static function mapaMontos(): Collection
    {
        return static::ultimasPorTipo()->map(fn (TasaCambio $t) => (float) $t->monto);
    }
}
