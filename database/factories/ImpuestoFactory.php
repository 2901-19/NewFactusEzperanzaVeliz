<?php

namespace Database\Factories;

use App\Models\Impuesto;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImpuestoFactory extends Factory
{
    protected $model = Impuesto::class;

    public function definition(): array
    {
        return [
            'nombre' => 'IVA',
            'porcentaje' => 16.00,
            'fecha' => fake()->date('Y-m-d'),
        ];
    }
}
