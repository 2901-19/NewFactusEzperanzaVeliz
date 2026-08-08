<?php

namespace App\Services;

class CatalogoService
{
    public const UMBRAL_STOCK_BAJO = 10;

    public const METODOS_PAGO = [
        'efectivo' => 'Efectivo',
        'punto' => 'Punto de Venta',
        'biopago' => 'Biopago',
        'divisas' => 'Divisas',
        'pago_movil' => 'Pago Móvil',
        'transferencia' => 'Transferencia',
        'mixto' => 'Mixto',
        'credito' => 'Crédito',
    ];

    public static function metodosPago(): array
    {
        return self::METODOS_PAGO;
    }

    public static function nombreMetodo(string $metodo): ?string
    {
        return self::METODOS_PAGO[$metodo] ?? null;
    }

    public static function metodosValidos(): array
    {
        return array_keys(array_diff_key(self::METODOS_PAGO, ['mixto' => true, 'credito' => true]));
    }
}
