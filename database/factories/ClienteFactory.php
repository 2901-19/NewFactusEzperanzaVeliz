<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'ci' => fake()->unique()->numerify('V########'),
            'nombre' => fake()->name(),
            'telefono' => fake()->phoneNumber(),
        ];
    }
}
