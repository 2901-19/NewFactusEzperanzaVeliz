<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            ['nombre' => 'Arroz Blanquito 1kg', 'unidades_por_paquete' => 12, 'precio_unitario_usd' => 1.20, 'precio_mayor_usd' => 1.05, 'cantidad_minima_mayor' => 12],
            ['nombre' => 'Harina PAN 1kg', 'unidades_por_paquete' => 12, 'precio_unitario_usd' => 2.10, 'precio_mayor_usd' => 1.85, 'cantidad_minima_mayor' => 12],
            ['nombre' => 'Café La Fina 250g', 'unidades_por_paquete' => 24, 'precio_unitario_usd' => 2.80, 'precio_mayor_usd' => 2.50, 'cantidad_minima_mayor' => 12],
            ['nombre' => 'Coca-Cola 2L', 'unidades_por_paquete' => 6, 'precio_unitario_usd' => 2.00, 'precio_mayor_usd' => 1.75, 'cantidad_minima_mayor' => 6],
            ['nombre' => 'Leche Completa 1L', 'unidades_por_paquete' => 6, 'precio_unitario_usd' => 2.50, 'precio_mayor_usd' => 2.25, 'cantidad_minima_mayor' => 6],
            ['nombre' => 'Huevos Blancos Cartón 30', 'unidades_por_paquete' => 1, 'precio_unitario_usd' => 4.00, 'precio_mayor_usd' => 3.70, 'cantidad_minima_mayor' => 5],
            ['nombre' => 'Jabón Dove 90g', 'unidades_por_paquete' => 24, 'precio_unitario_usd' => 0.80, 'precio_mayor_usd' => 0.65, 'cantidad_minima_mayor' => 12],
            ['nombre' => 'Pasta Dental Colgate 75ml', 'unidades_por_paquete' => 12, 'precio_unitario_usd' => 2.00, 'precio_mayor_usd' => 1.75, 'cantidad_minima_mayor' => 12],
            ['nombre' => 'Detergente Fab 1kg', 'unidades_por_paquete' => 6, 'precio_unitario_usd' => 3.00, 'precio_mayor_usd' => 2.70, 'cantidad_minima_mayor' => 6],
            ['nombre' => 'Cloro 1L', 'unidades_por_paquete' => 6, 'precio_unitario_usd' => 1.50, 'precio_mayor_usd' => 1.30, 'cantidad_minima_mayor' => 6],
            ['nombre' => 'Queso Dambo 500g', 'unidades_por_paquete' => 1, 'precio_unitario_usd' => 4.50, 'precio_mayor_usd' => 4.00, 'cantidad_minima_mayor' => 5],
            ['nombre' => 'Papas Lays 100g', 'unidades_por_paquete' => 24, 'precio_unitario_usd' => 1.30, 'precio_mayor_usd' => 1.05, 'cantidad_minima_mayor' => 12],
        ];

        $fuentes = ['promedio', 'dolar', 'bcv'];

        foreach ($productos as $i => $p) {
            Producto::firstOrCreate(
                ['nombre' => $p['nombre']],
                [
                    'descripcion' => null,
                    'unidades_por_paquete' => $p['unidades_por_paquete'],
                    'stock_paquetes' => rand(5, 30),
                    'stock_unidades' => rand(0, $p['unidades_por_paquete']),
                    'precio_unitario_usd' => $p['precio_unitario_usd'],
                    'precio_mayor_usd' => $p['precio_mayor_usd'],
                    'cantidad_minima_mayor' => $p['cantidad_minima_mayor'],
                    'tiene_iva' => true,
                    'fuente_tasa' => $fuentes[array_rand($fuentes)],
                    'estado' => 'disponible',
                ]
            );
        }

        $this->command->info('✓ ' . count($productos) . ' productos creados');
    }
}
