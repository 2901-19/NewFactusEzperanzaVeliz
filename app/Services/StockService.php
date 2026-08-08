<?php

namespace App\Services;

use App\Models\Producto;

class StockService
{
    public static function totalUnidades(Producto $producto): int
    {
        return ($producto->stock_paquetes * $producto->unidades_por_paquete) + $producto->stock_unidades;
    }

    public static function descomponer(int $totalUnidades, int $unidadesPorPaquete): array
    {
        if ($unidadesPorPaquete <= 0) {
            throw new \InvalidArgumentException('El producto debe tener al menos 1 unidad por paquete para ajustar inventario.');
        }

        return [
            'stock_paquetes' => intdiv($totalUnidades, $unidadesPorPaquete),
            'stock_unidades' => $totalUnidades % $unidadesPorPaquete,
        ];
    }

    public static function descontar(Producto $producto, int $cantidad): void
    {
        $restantes = $cantidad;

        if ($producto->stock_unidades >= $restantes) {
            $producto->decrement('stock_unidades', $restantes);

            return;
        }

        $restantes -= $producto->stock_unidades;
        $producto->update(['stock_unidades' => 0]);

        $paquetesNecesarios = (int) ceil($restantes / $producto->unidades_por_paquete);

        if ($producto->stock_paquetes < $paquetesNecesarios) {
            throw new \RuntimeException("Stock insuficiente para {$producto->nombre}");
        }

        $producto->decrement('stock_paquetes', $paquetesNecesarios);

        $unidadesGeneradas = $paquetesNecesarios * $producto->unidades_por_paquete;
        $sobrantes = $unidadesGeneradas - $restantes;

        if ($sobrantes > 0) {
            $producto->increment('stock_unidades', $sobrantes);
        }
    }
}
