<?php

namespace Database\Seeders;

use App\Models\TasaCambio;
use Illuminate\Database\Seeder;

class TasaCambioSeeder extends Seeder
{
    public function run(): void
    {
        $tasas = [
            ['tipo' => 'promedio', 'moneda' => 'USD', 'monto' => 59.25],
            ['tipo' => 'dolar',    'moneda' => 'USD', 'monto' => 60.00],
            ['tipo' => 'bcv',      'moneda' => 'USD', 'monto' => 58.50],
        ];

        foreach ($tasas as $tasa) {
            TasaCambio::updateOrCreate(
                ['tipo' => $tasa['tipo']],
                [
                    'moneda' => $tasa['moneda'],
                    'monto' => $tasa['monto'],
                    'fecha' => now()->toDateString(),
                ]
            );
        }

        $this->command->info('✓ 3 tasas de cambio creadas (promedio, dolar, bcv)');
    }
}
