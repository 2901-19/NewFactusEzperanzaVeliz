<?php

namespace Database\Seeders;

use App\Models\TasaCambio;
use Illuminate\Database\Seeder;

class TasaCambioSeeder extends Seeder
{
    public function run(): void
    {
        // Histórico de tasas de los últimos 60 días
        $fecha = now()->subDays(60);
        $hoy = now();

        $dolar = 60.00;
        $bcv = 58.50;
        $promedio = 59.25;

        while ($fecha <= $hoy) {
            $dolar += round((rand(-100, 150) / 100), 2);
            $bcv += round((rand(-80, 120) / 100), 2);
            $promedio = round(($dolar + $bcv) / 2, 2);

            foreach (['dolar' => $dolar, 'bcv' => $bcv, 'promedio' => $promedio] as $tipo => $monto) {
                TasaCambio::firstOrCreate(
                    ['tipo' => $tipo, 'fecha' => $fecha->toDateString()],
                    ['moneda' => 'USD', 'monto' => max($monto, 1)]
                );
            }

            $fecha->addDay();
        }

        $this->command->info('✓ 3 tasas/día por 60 días creadas (~180 registros)');
    }
}
