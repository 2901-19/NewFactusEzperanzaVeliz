<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            // Alimentos / Básicos
            ['nombre' => 'Arroz Blanquito 1kg', 'unidades_por_paquete' => 12, 'precio_unitario_usd' => 1.20, 'precio_mayor_usd' => 1.05, 'cantidad_minima_mayor' => 12],
            ['nombre' => 'Harina PAN 1kg', 'unidades_por_paquete' => 12, 'precio_unitario_usd' => 2.10, 'precio_mayor_usd' => 1.85, 'cantidad_minima_mayor' => 12],
            ['nombre' => 'Azúcar La Montaña 1kg', 'unidades_por_paquete' => 10, 'precio_unitario_usd' => 1.15, 'precio_mayor_usd' => 1.00, 'cantidad_minima_mayor' => 10],
            ['nombre' => 'Café La Fina 250g', 'unidades_por_paquete' => 24, 'precio_unitario_usd' => 2.80, 'precio_mayor_usd' => 2.50, 'cantidad_minima_mayor' => 12],
            ['nombre' => 'Aceite Mazola 1L', 'unidades_por_paquete' => 6, 'precio_unitario_usd' => 3.50, 'precio_mayor_usd' => 3.20, 'cantidad_minima_mayor' => 6],
            ['nombre' => 'Pasta La Italiana 500g', 'unidades_por_paquete' => 20, 'precio_unitario_usd' => 0.90, 'precio_mayor_usd' => 0.75, 'cantidad_minima_mayor' => 20],
            ['nombre' => 'Lata Atún Margarita 170g', 'unidades_por_paquete' => 24, 'precio_unitario_usd' => 1.80, 'precio_mayor_usd' => 1.55, 'cantidad_minima_mayor' => 12],
            ['nombre' => 'Leche Completa 1L', 'unidades_por_paquete' => 6, 'precio_unitario_usd' => 2.50, 'precio_mayor_usd' => 2.25, 'cantidad_minima_mayor' => 6],
            ['nombre' => 'Huevos Blancos Cartón 30', 'unidades_por_paquete' => 1, 'precio_unitario_usd' => 4.00, 'precio_mayor_usd' => 3.70, 'cantidad_minima_mayor' => 5],
            ['nombre' => 'Margarina Mavesa 500g', 'unidades_por_paquete' => 12, 'precio_unitario_usd' => 1.95, 'precio_mayor_usd' => 1.70, 'cantidad_minima_mayor' => 12],
            ['nombre' => 'Queso Dambo 500g', 'unidades_por_paquete' => 1, 'precio_unitario_usd' => 4.50, 'precio_mayor_usd' => 4.00, 'cantidad_minima_mayor' => 5],
            ['nombre' => 'Pan Bimbo Grande 600g', 'unidades_por_paquete' => 6, 'precio_unitario_usd' => 3.20, 'precio_mayor_usd' => 2.90, 'cantidad_minima_mayor' => 6],
            ['nombre' => 'Sal Marina 1kg', 'unidades_por_paquete' => 12, 'precio_unitario_usd' => 0.60, 'precio_mayor_usd' => 0.50, 'cantidad_minima_mayor' => 12],
            ['nombre' => 'Galleta María 400g', 'unidades_por_paquete' => 12, 'precio_unitario_usd' => 1.50, 'precio_mayor_usd' => 1.30, 'cantidad_minima_mayor' => 12],
            ['nombre' => 'Cereal Corn Flakes 300g', 'unidades_por_paquete' => 12, 'precio_unitario_usd' => 2.20, 'precio_mayor_usd' => 1.95, 'cantidad_minima_mayor' => 12],

            // Bebidas
            ['nombre' => 'Coca-Cola 2L', 'unidades_por_paquete' => 6, 'precio_unitario_usd' => 2.00, 'precio_mayor_usd' => 1.75, 'cantidad_minima_mayor' => 6],
            ['nombre' => 'Pepsi 1.5L', 'unidades_por_paquete' => 6, 'precio_unitario_usd' => 1.80, 'precio_mayor_usd' => 1.55, 'cantidad_minima_mayor' => 6],
            ['nombre' => 'Maltín Polar 355ml Lata', 'unidades_por_paquete' => 24, 'precio_unitario_usd' => 1.20, 'precio_mayor_usd' => 1.00, 'cantidad_minima_mayor' => 24],
            ['nombre' => 'Agua Minalba 1.5L', 'unidades_por_paquete' => 12, 'precio_unitario_usd' => 1.00, 'precio_mayor_usd' => 0.85, 'cantidad_minima_mayor' => 12],
            ['nombre' => 'Jugo Tampico 1L', 'unidades_por_paquete' => 6, 'precio_unitario_usd' => 1.60, 'precio_mayor_usd' => 1.40, 'cantidad_minima_mayor' => 6],

            // Licores
            ['nombre' => 'Ron Pampero 750ml', 'unidades_por_paquete' => 6, 'precio_unitario_usd' => 10.00, 'precio_mayor_usd' => 8.50, 'cantidad_minima_mayor' => 6],
            ['nombre' => 'Polar Cero 330ml Lata', 'unidades_por_paquete' => 24, 'precio_unitario_usd' => 1.00, 'precio_mayor_usd' => 0.80, 'cantidad_minima_mayor' => 24],
            ['nombre' => 'Polar Light 330ml Lata', 'unidades_por_paquete' => 24, 'precio_unitario_usd' => 1.00, 'precio_mayor_usd' => 0.80, 'cantidad_minima_mayor' => 24],
            ['nombre' => 'Whisky Buchanan\'s 750ml', 'unidades_por_paquete' => 6, 'precio_unitario_usd' => 28.00, 'precio_mayor_usd' => 25.00, 'cantidad_minima_mayor' => 3],
            ['nombre' => 'Vino Tinto 750ml', 'unidades_por_paquete' => 6, 'precio_unitario_usd' => 6.00, 'precio_mayor_usd' => 5.00, 'cantidad_minima_mayor' => 6],

            // Aseo Personal
            ['nombre' => 'Jabón Dove 90g', 'unidades_por_paquete' => 24, 'precio_unitario_usd' => 0.80, 'precio_mayor_usd' => 0.65, 'cantidad_minima_mayor' => 12],
            ['nombre' => 'Pasta Dental Colgate 75ml', 'unidades_por_paquete' => 12, 'precio_unitario_usd' => 2.00, 'precio_mayor_usd' => 1.75, 'cantidad_minima_mayor' => 12],
            ['nombre' => 'Shampoo Pantene 200ml', 'unidades_por_paquete' => 12, 'precio_unitario_usd' => 3.50, 'precio_mayor_usd' => 3.00, 'cantidad_minima_mayor' => 12],
            ['nombre' => 'Desodorante Axe 150ml', 'unidades_por_paquete' => 12, 'precio_unitario_usd' => 2.80, 'precio_mayor_usd' => 2.40, 'cantidad_minima_mayor' => 6],
            ['nombre' => 'Papel Higiénico Scottex 4rollos', 'unidades_por_paquete' => 6, 'precio_unitario_usd' => 2.50, 'precio_mayor_usd' => 2.20, 'cantidad_minima_mayor' => 6],

            // Limpieza
            ['nombre' => 'Cloro 1L', 'unidades_por_paquete' => 6, 'precio_unitario_usd' => 1.50, 'precio_mayor_usd' => 1.30, 'cantidad_minima_mayor' => 6],
            ['nombre' => 'Detergente Fab 1kg', 'unidades_por_paquete' => 6, 'precio_unitario_usd' => 3.00, 'precio_mayor_usd' => 2.70, 'cantidad_minima_mayor' => 6],
            ['nombre' => 'Desinfectante Pino 1L', 'unidades_por_paquete' => 6, 'precio_unitario_usd' => 2.00, 'precio_mayor_usd' => 1.75, 'cantidad_minima_mayor' => 6],
            ['nombre' => 'Lavaplatos Axión 500ml', 'unidades_por_paquete' => 12, 'precio_unitario_usd' => 1.80, 'precio_mayor_usd' => 1.55, 'cantidad_minima_mayor' => 12],
            ['nombre' => 'Esponja Scotch-Brite', 'unidades_por_paquete' => 24, 'precio_unitario_usd' => 0.50, 'precio_mayor_usd' => 0.40, 'cantidad_minima_mayor' => 12],

            // Mascotas
            ['nombre' => 'Comida Perro Dogui 1kg', 'unidades_por_paquete' => 6, 'precio_unitario_usd' => 4.00, 'precio_mayor_usd' => 3.50, 'cantidad_minima_mayor' => 6],
            ['nombre' => 'Comida Gato CatChow 1kg', 'unidades_por_paquete' => 6, 'precio_unitario_usd' => 4.50, 'precio_mayor_usd' => 4.00, 'cantidad_minima_mayor' => 6],

            // Electrónicos / Pilas
            ['nombre' => 'Pila AA Duracell 2u', 'unidades_por_paquete' => 24, 'precio_unitario_usd' => 1.50, 'precio_mayor_usd' => 1.25, 'cantidad_minima_mayor' => 12],
            ['nombre' => 'Bombillo Led 12W', 'unidades_por_paquete' => 10, 'precio_unitario_usd' => 2.50, 'precio_mayor_usd' => 2.10, 'cantidad_minima_mayor' => 5],

            // Golosinas / Snacks
            ['nombre' => 'Chocolate Savoy 100g', 'unidades_por_paquete' => 24, 'precio_unitario_usd' => 1.20, 'precio_mayor_usd' => 1.00, 'cantidad_minima_mayor' => 12],
            ['nombre' => 'Papas Lays 100g', 'unidades_por_paquete' => 24, 'precio_unitario_usd' => 1.30, 'precio_mayor_usd' => 1.05, 'cantidad_minima_mayor' => 12],
            ['nombre' => 'Chicle Bocabits 30g', 'unidades_por_paquete' => 48, 'precio_unitario_usd' => 0.30, 'precio_mayor_usd' => 0.20, 'cantidad_minima_mayor' => 24],
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
