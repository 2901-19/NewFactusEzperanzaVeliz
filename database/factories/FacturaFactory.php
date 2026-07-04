<?php

namespace Database\Factories;

use App\Models\Factura;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacturaFactory extends Factory
{
    protected $model = Factura::class;

    public function definition(): array
    {
        return [
            'correlativo' => strtoupper(fake()->unique()->bothify('FAC-#####')),
            'cliente_id' => Cliente::factory(),
            'user_id' => User::factory(),
            'productos' => json_encode([]),
            'tasa_cambio' => fake()->randomFloat(2, 40, 60),
            'metodo_pago' => fake()->randomElement(['efectivo', 'pago_movil', 'divisas']),
            'subtotal_bs' => fake()->randomFloat(2, 1000, 10000),
            'iva_bs' => fake()->randomFloat(2, 100, 1000),
            'total_bs' => fake()->randomFloat(2, 1100, 11000),
            'total_usd' => fake()->randomFloat(2, 20, 200),
            'estado' => 'contado',
            'estado_credito' => null,
            'fecha_venta' => fake()->date('Y-m-d'),
        ];
    }

    public function credito(): static
    {
        return $this->state(fn(array $attrs) => [
            'estado' => 'credito',
            'estado_credito' => 'pendiente',
        ]);
    }

    public function anulada(): static
    {
        return $this->state(fn(array $attrs) => [
            'estado' => 'anulada',
            'estado_credito' => null,
        ]);
    }
}
