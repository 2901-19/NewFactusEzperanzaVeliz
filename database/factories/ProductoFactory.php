<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->word(),
            'categoria_id' => Categoria::factory(),
            'unidades_por_paquete' => fake()->randomElement([6, 12, 24, 50]),
            'stock_paquetes' => fake()->numberBetween(0, 20),
            'stock_unidades' => fake()->numberBetween(0, 50),
            'costo_usd' => fake()->randomFloat(2, 0.3, 40),
            'margen_detal' => fake()->randomElement([15, 20, 25, 30]),
            'margen_mayor' => fake()->randomElement([10, 12, 15, 20]),
            'precio_unitario_usd' => fake()->randomFloat(2, 0.5, 50),
            'precio_mayor_usd' => fake()->randomFloat(2, 0.3, 40),
            'tiene_iva' => true,
            'fuente_tasa' => fake()->randomElement(['promedio', 'dolar', 'bcv']),
            'estado' => 'disponible',
        ];
    }
}
