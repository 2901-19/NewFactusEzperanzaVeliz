<?php

namespace App\Services;

use App\Models\Producto;

class StockService
{
    public static function stockActual(Producto $producto): float
    {
        return (float) $producto->stock_actual;
    }

    public static function descontar(Producto $producto, float $cantidadBase): void
    {
        if ($cantidadBase <= 0) {
            return;
        }

        if (! $producto->controla_inventario) {
            return;
        }

        if (self::stockActual($producto) < $cantidadBase) {
            throw new \RuntimeException(sprintf(
                'Stock insuficiente para %s. Disponible: %s %s.',
                $producto->nombre,
                rtrim(rtrim(number_format(self::stockActual($producto), 4, '.', ''), '0'), '.'),
                $producto->unidad_medida
            ));
        }

        $producto->decrement('stock_actual', $cantidadBase);
    }

    public static function agregar(Producto $producto, float $cantidadBase): void
    {
        if ($cantidadBase <= 0) {
            return;
        }

        if (! $producto->controla_inventario) {
            return;
        }

        $producto->increment('stock_actual', $cantidadBase);
    }
}
