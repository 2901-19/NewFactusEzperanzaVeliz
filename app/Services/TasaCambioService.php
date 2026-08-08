<?php

namespace App\Services;

use App\Models\TasaCambio;

class TasaCambioService
{
    public static function monto(string $tipo): ?float
    {
        $tasa = TasaCambio::ultimaDe($tipo);

        return $tasa ? (float) $tasa->monto : null;
    }

    public static function montoOException(string $tipo): float
    {
        $monto = self::monto($tipo);

        if (! $monto || $monto <= 0) {
            throw new \RuntimeException("La tasa de cambio '{$tipo}' no está configurada. Actualicela antes de vender.");
        }

        return $monto;
    }

    public static function convertirUsdABs(float $montoUsd, string $tipo): float
    {
        return round($montoUsd * self::montoOException($tipo), 2);
    }
}
