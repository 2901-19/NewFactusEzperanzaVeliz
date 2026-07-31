<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            ['nombre' => 'Arroz Blanquito 1kg', 'unidades_por_paquete' => 12, 'costo_usd' => 0.96, 'margen_detal' => 25, 'margen_mayor' => 15],
            ['nombre' => 'Harina PAN 1kg', 'unidades_por_paquete' => 12, 'costo_usd' => 1.68, 'margen_detal' => 25, 'margen_mayor' => 15],
            ['nombre' => 'Café La Fina 250g', 'unidades_por_paquete' => 24, 'costo_usd' => 2.24, 'margen_detal' => 25, 'margen_mayor' => 15],
            ['nombre' => 'Coca-Cola 2L', 'unidades_por_paquete' => 6, 'costo_usd' => 1.60, 'margen_detal' => 25, 'margen_mayor' => 15],
            ['nombre' => 'Leche Completa 1L', 'unidades_por_paquete' => 6, 'costo_usd' => 2.00, 'margen_detal' => 25, 'margen_mayor' => 15],
            ['nombre' => 'Huevos Blancos Cartón 30', 'unidades_por_paquete' => 1, 'costo_usd' => 3.20, 'margen_detal' => 25, 'margen_mayor' => 15],
            ['nombre' => 'Jabón Dove 90g', 'unidades_por_paquete' => 24, 'costo_usd' => 0.64, 'margen_detal' => 25, 'margen_mayor' => 15],
            ['nombre' => 'Pasta Dental Colgate 75ml', 'unidades_por_paquete' => 12, 'costo_usd' => 1.60, 'margen_detal' => 25, 'margen_mayor' => 15],
            ['nombre' => 'Detergente Fab 1kg', 'unidades_por_paquete' => 6, 'costo_usd' => 2.40, 'margen_detal' => 25, 'margen_mayor' => 15],
            ['nombre' => 'Cloro 1L', 'unidades_por_paquete' => 6, 'costo_usd' => 1.20, 'margen_detal' => 25, 'margen_mayor' => 15],
            ['nombre' => 'Queso Dambo 500g', 'unidades_por_paquete' => 1, 'costo_usd' => 3.60, 'margen_detal' => 25, 'margen_mayor' => 15],
            ['nombre' => 'Papas Lays 100g', 'unidades_por_paquete' => 24, 'costo_usd' => 1.04, 'margen_detal' => 25, 'margen_mayor' => 15],
        ];

        $fuentes = ['promedio', 'dolar', 'bcv'];

        foreach ($productos as $p) {
            $precioUnitario = round($p['costo_usd'] * (1 + $p['margen_detal'] / 100), 2);
            $precioMayor = round($p['costo_usd'] * (1 + $p['margen_mayor'] / 100), 2);

            Producto::firstOrCreate(
                ['nombre' => $p['nombre']],
                [
                    'descripcion' => null,
                    'unidades_por_paquete' => $p['unidades_por_paquete'],
                    'stock_paquetes' => rand(5, 30),
                    'stock_unidades' => rand(0, $p['unidades_por_paquete']),
                    'costo_usd' => $p['costo_usd'],
                    'margen_detal' => $p['margen_detal'],
                    'margen_mayor' => $p['margen_mayor'],
                    'precio_unitario_usd' => $precioUnitario,
                    'precio_mayor_usd' => $precioMayor,
                    'tiene_iva' => false,
                    'fuente_tasa' => $fuentes[array_rand($fuentes)],
                    'estado' => 'disponible',
                ]
            );
        }

        $this->command->info('✓ ' . count($productos) . ' productos creados');
    }
}
