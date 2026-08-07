<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = Categoria::pluck('id', 'nombre');

        $productos = [
            // Alimentos
            ['nombre' => 'Arroz Blanquito 1kg', 'cat' => 'Alimentos', 'upp' => 12, 'costo' => 0.96, 'detal' => 25, 'mayor' => 15, 'fuente' => 'promedio'],
            ['nombre' => 'Harina PAN 1kg', 'cat' => 'Alimentos', 'upp' => 12, 'costo' => 1.68, 'detal' => 25, 'mayor' => 15, 'fuente' => 'promedio'],
            ['nombre' => 'Pasta Espagueti 500g', 'cat' => 'Alimentos', 'upp' => 24, 'costo' => 0.72, 'detal' => 28, 'mayor' => 16, 'fuente' => 'dolar'],
            ['nombre' => 'Café La Fina 250g', 'cat' => 'Alimentos', 'upp' => 24, 'costo' => 2.24, 'detal' => 25, 'mayor' => 15, 'fuente' => 'promedio'],
            ['nombre' => 'Azúcar Refinada 1kg', 'cat' => 'Alimentos', 'upp' => 12, 'costo' => 1.20, 'detal' => 22, 'mayor' => 12, 'fuente' => 'promedio'],
            ['nombre' => 'Aceite de Maíz 1L', 'cat' => 'Alimentos', 'upp' => 6, 'costo' => 2.40, 'detal' => 25, 'mayor' => 15, 'fuente' => 'dolar'],
            ['nombre' => 'Atún Delfín 170g', 'cat' => 'Alimentos', 'upp' => 24, 'costo' => 1.52, 'detal' => 30, 'mayor' => 18, 'fuente' => 'bcv'],
            ['nombre' => 'Sardinas La Sirena 170g', 'cat' => 'Alimentos', 'upp' => 24, 'costo' => 1.04, 'detal' => 30, 'mayor' => 18, 'fuente' => 'bcv'],
            ['nombre' => 'Avena Quaker 500g', 'cat' => 'Alimentos', 'upp' => 12, 'costo' => 1.28, 'detal' => 25, 'mayor' => 15, 'fuente' => 'promedio'],
            ['nombre' => 'Mayonesa Mavesa 445g', 'cat' => 'Alimentos', 'upp' => 12, 'costo' => 1.92, 'detal' => 25, 'mayor' => 15, 'fuente' => 'promedio'],
            ['nombre' => 'Frijoles Negros 500g', 'cat' => 'Alimentos', 'upp' => 12, 'costo' => 1.12, 'detal' => 28, 'mayor' => 16, 'fuente' => 'promedio'],
            ['nombre' => 'Pan de Molde 700g', 'cat' => 'Alimentos', 'upp' => 1, 'costo' => 1.76, 'detal' => 30, 'mayor' => 18, 'fuente' => 'promedio'],
            // Bebidas
            ['nombre' => 'Coca-Cola 2L', 'cat' => 'Bebidas', 'upp' => 6, 'costo' => 1.60, 'detal' => 25, 'mayor' => 15, 'fuente' => 'promedio'],
            ['nombre' => 'Pepsi 2L', 'cat' => 'Bebidas', 'upp' => 6, 'costo' => 1.44, 'detal' => 25, 'mayor' => 15, 'fuente' => 'promedio'],
            ['nombre' => 'Malta Polar 310ml', 'cat' => 'Bebidas', 'upp' => 24, 'costo' => 0.96, 'detal' => 28, 'mayor' => 16, 'fuente' => 'promedio'],
            ['nombre' => 'Jugo Del Monte 1L', 'cat' => 'Bebidas', 'upp' => 6, 'costo' => 1.36, 'detal' => 25, 'mayor' => 15, 'fuente' => 'dolar'],
            ['nombre' => 'Agua Mineral 1L', 'cat' => 'Bebidas', 'upp' => 12, 'costo' => 0.40, 'detal' => 30, 'mayor' => 18, 'fuente' => 'bcv'],
            ['nombre' => 'Agua Mineral 5L', 'cat' => 'Bebidas', 'upp' => 4, 'costo' => 1.12, 'detal' => 25, 'mayor' => 15, 'fuente' => 'promedio'],
            // Lácteos
            ['nombre' => 'Leche Completa 1L', 'cat' => 'Lácteos', 'upp' => 6, 'costo' => 2.00, 'detal' => 25, 'mayor' => 15, 'fuente' => 'promedio'],
            ['nombre' => 'Leche en Polvo 400g', 'cat' => 'Lácteos', 'upp' => 12, 'costo' => 4.00, 'detal' => 25, 'mayor' => 15, 'fuente' => 'promedio'],
            ['nombre' => 'Queso Dambo 500g', 'cat' => 'Lácteos', 'upp' => 1, 'costo' => 3.60, 'detal' => 25, 'mayor' => 15, 'fuente' => 'promedio'],
            ['nombre' => 'Margarina 500g', 'cat' => 'Lácteos', 'upp' => 12, 'costo' => 1.44, 'detal' => 28, 'mayor' => 16, 'fuente' => 'promedio'],
            ['nombre' => 'Huevos Blancos Cartón 30', 'cat' => 'Lácteos', 'upp' => 1, 'costo' => 3.20, 'detal' => 25, 'mayor' => 15, 'fuente' => 'bcv'],
            ['nombre' => 'Yogurt Bebible 1L', 'cat' => 'Lácteos', 'upp' => 6, 'costo' => 1.60, 'detal' => 25, 'mayor' => 15, 'fuente' => 'dolar'],
            // Aseo Personal
            ['nombre' => 'Jabón Dove 90g', 'cat' => 'Aseo Personal', 'upp' => 24, 'costo' => 0.64, 'detal' => 25, 'mayor' => 15, 'fuente' => 'promedio'],
            ['nombre' => 'Jabón Protector 125g', 'cat' => 'Aseo Personal', 'upp' => 24, 'costo' => 0.40, 'detal' => 30, 'mayor' => 18, 'fuente' => 'promedio'],
            ['nombre' => 'Pasta Dental Colgate 75ml', 'cat' => 'Aseo Personal', 'upp' => 12, 'costo' => 1.60, 'detal' => 25, 'mayor' => 15, 'fuente' => 'promedio'],
            ['nombre' => 'Cepillo Dental Colgate', 'cat' => 'Aseo Personal', 'upp' => 12, 'costo' => 0.88, 'detal' => 30, 'mayor' => 18, 'fuente' => 'dolar'],
            ['nombre' => 'Shampoo Pantene 400ml', 'cat' => 'Aseo Personal', 'upp' => 12, 'costo' => 2.40, 'detal' => 25, 'mayor' => 15, 'fuente' => 'promedio'],
            ['nombre' => 'Desodorante Axe 90ml', 'cat' => 'Aseo Personal', 'upp' => 12, 'costo' => 2.08, 'detal' => 25, 'mayor' => 15, 'fuente' => 'bcv'],
            // Limpieza
            ['nombre' => 'Detergente Fab 1kg', 'cat' => 'Limpieza', 'upp' => 6, 'costo' => 2.40, 'detal' => 25, 'mayor' => 15, 'fuente' => 'promedio'],
            ['nombre' => 'Cloro 1L', 'cat' => 'Limpieza', 'upp' => 6, 'costo' => 1.20, 'detal' => 25, 'mayor' => 15, 'fuente' => 'promedio'],
            ['nombre' => 'Desinfectante Pino 1L', 'cat' => 'Limpieza', 'upp' => 6, 'costo' => 1.52, 'detal' => 25, 'mayor' => 15, 'fuente' => 'promedio'],
            ['nombre' => 'Jabón Las Llaves 500g', 'cat' => 'Limpieza', 'upp' => 12, 'costo' => 1.04, 'detal' => 28, 'mayor' => 16, 'fuente' => 'bcv'],
            ['nombre' => 'Papel Toalla 2 Rollos', 'cat' => 'Limpieza', 'upp' => 6, 'costo' => 1.76, 'detal' => 25, 'mayor' => 15, 'fuente' => 'promedio'],
            ['nombre' => 'Papel Higiénico 4 Rollos', 'cat' => 'Limpieza', 'upp' => 4, 'costo' => 2.40, 'detal' => 25, 'mayor' => 15, 'fuente' => 'promedio'],
            ['nombre' => 'Bolsas de Basura', 'cat' => 'Limpieza', 'upp' => 6, 'costo' => 0.80, 'detal' => 30, 'mayor' => 18, 'fuente' => 'promedio'],
            // Botanas
            ['nombre' => 'Papas Lays 100g', 'cat' => 'Botanas', 'upp' => 24, 'costo' => 1.04, 'detal' => 25, 'mayor' => 15, 'fuente' => 'promedio'],
            ['nombre' => 'Galletas María 400g', 'cat' => 'Botanas', 'upp' => 24, 'costo' => 0.88, 'detal' => 25, 'mayor' => 15, 'fuente' => 'promedio'],
            ['nombre' => 'Palitos de Maíz 100g', 'cat' => 'Botanas', 'upp' => 24, 'costo' => 0.72, 'detal' => 28, 'mayor' => 16, 'fuente' => 'dolar'],
        ];

        $total = 0;
        foreach ($productos as $p) {
            $precioUnitario = round($p['costo'] * (1 + $p['detal'] / 100), 2);
            $precioMayor = round($p['costo'] * (1 + $p['mayor'] / 100), 2);

            Producto::updateOrCreate(
                ['nombre' => $p['nombre']],
                [
                    'categoria_id' => $categorias[$p['cat']] ?? null,
                    'descripcion' => null,
                    'unidades_por_paquete' => $p['upp'],
                    'stock_paquetes' => rand(10, 40),
                    'stock_unidades' => rand(0, $p['upp'] - 1),
                    'costo_usd' => $p['costo'],
                    'margen_detal' => $p['detal'],
                    'margen_mayor' => $p['mayor'],
                    'precio_unitario_usd' => $precioUnitario,
                    'precio_mayor_usd' => $precioMayor,
                    'tiene_iva' => false,
                    'fuente_tasa' => $p['fuente'],
                    'estado' => 'disponible',
                ]
            );
            $total++;
        }

        $this->command->info("✓ $total productos creados");
    }
}
