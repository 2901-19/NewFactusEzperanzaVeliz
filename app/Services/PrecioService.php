<?php

namespace App\Services;

class PrecioService
{
    /**
     * Precio de una presentación: margen aplicado sobre el costo unitario
     * (redondeado a 2 decimales) y multiplicado por el factor de conversión.
     */
    public static function precioPresentacion(float $costoUsd, float $margen, float $factor): float
    {
        $precioUnidad = round($costoUsd * (1 + $margen / 100), 2);

        return round($precioUnidad * $factor, 2);
    }

    public static function precioBase(float $costoUsd, float $margen): float
    {
        return round($costoUsd * (1 + $margen / 100), 2);
    }
}
