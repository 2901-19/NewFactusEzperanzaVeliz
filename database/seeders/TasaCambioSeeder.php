<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use App\Models\TasaCambio;
use Illuminate\Database\Seeder;

class TasaCambioSeeder extends Seeder
{
    public function run(): void
    {
        $tasas = [
            ['tipo' => 'promedio', 'nombre' => 'Promedio', 'monto' => 59.25],
            ['tipo' => 'dolar', 'nombre' => 'Dólar Paralelo', 'monto' => 60.00],
            ['tipo' => 'bcv', 'nombre' => 'BCV', 'monto' => 58.50],
        ];

        foreach ($tasas as $tasa) {
            $vigente = TasaCambio::ultimaDe($tasa['tipo']);

            if ($vigente) {
                $vigente->update([
                    'nombre' => $tasa['nombre'],
                    'monto' => $tasa['monto'],
                    'fecha' => now()->toDateString(),
                    'activo' => true,
                ]);
            } else {
                TasaCambio::create($tasa + ['fecha' => now()->toDateString(), 'activo' => true]);
            }
        }

        Configuracion::updateOrCreate(
            ['clave' => 'tasa_referencia'],
            ['valor' => 'bcv']
        );

        $this->command->info('✓ 3 tasas de cambio creadas (promedio, dolar, bcv) y referencia por defecto BCV');
    }
}
