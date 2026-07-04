<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            ['ci' => 'V-12345678', 'nombre' => 'Carlos Méndez', 'telefono' => '0412-1234567'],
            ['ci' => 'V-23456789', 'nombre' => 'María Rodríguez', 'telefono' => '0416-2345678'],
            ['ci' => 'V-34567890', 'nombre' => 'José Contreras', 'telefono' => '0424-3456789'],
            ['ci' => 'V-45678901', 'nombre' => 'Ana Martínez', 'telefono' => '0412-4567890'],
            ['ci' => 'V-56789012', 'nombre' => 'Luis Hernández', 'telefono' => '0416-5678901'],
        ];

        foreach ($clientes as $c) {
            Cliente::firstOrCreate(
                ['ci' => $c['ci']],
                ['nombre' => $c['nombre'], 'telefono' => $c['telefono']]
            );
        }

        $this->command->info('✓ ' . count($clientes) . ' clientes creados');
    }
}
