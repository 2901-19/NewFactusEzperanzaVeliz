<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Impuesto;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->word(),
            'categoria_id' => Categoria::factory(),
            'costo_usd' => fake()->randomFloat(2, 0.3, 40),
            'stock_actual' => fake()->randomFloat(3, 0, 100),
            'unidad_medida' => 'unidad',
            'impuesto_id' => Impuesto::factory(),
            'fuente_tasa' => fake()->randomElement(['promedio', 'dolar', 'bcv']),
            'estado' => 'disponible',
        ];
    }
}
