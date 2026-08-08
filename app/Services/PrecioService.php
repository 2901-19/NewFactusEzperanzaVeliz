<?php

namespace App\Services;

class PrecioService
{
    public static function preciosDesdeMargenes(float $costoUsd, float $margenDetal, float $margenMayor): array
    {
        return [
            'precio_unitario_usd' => round($costoUsd * (1 + $margenDetal / 100), 2),
            'precio_mayor_usd' => round($costoUsd * (1 + $margenMayor / 100), 2),
        ];
    }
}
