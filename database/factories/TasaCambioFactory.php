<?php

namespace Database\Factories;

use App\Models\TasaCambio;
use Illuminate\Database\Eloquent\Factories\Factory;

class TasaCambioFactory extends Factory
{
    protected $model = TasaCambio::class;

    public function definition(): array
    {
        return [
            'tipo' => fake()->randomElement(['promedio', 'dolar', 'bcv']),
            'moneda' => 'Bs',
            'monto' => fake()->randomFloat(2, 40, 200),
            'fecha' => fake()->date('Y-m-d'),
        ];
    }
}
