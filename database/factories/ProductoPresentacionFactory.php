<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\ProductoPresentacion;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoPresentacionFactory extends Factory
{
    protected $model = ProductoPresentacion::class;

    public function definition(): array
    {
        return [
            'producto_id' => Producto::factory(),
            'nombre' => 'Unidad',
            'factor_conversion' => 1,
            'margen' => fake()->randomElement([15, 20, 25, 30]),
            'precio_usd' => fake()->randomFloat(2, 0.5, 50),
            'activa' => true,
        ];
    }
}
