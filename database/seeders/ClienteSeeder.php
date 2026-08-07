<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            // Personas naturales (detal)
            ['ci' => 'V-12345678', 'nombre' => 'Carlos Méndez', 'telefono' => '0412-1234567'],
            ['ci' => 'V-23456789', 'nombre' => 'María Rodríguez', 'telefono' => '0416-2345678'],
            ['ci' => 'V-34567890', 'nombre' => 'José Contreras', 'telefono' => '0424-3456789'],
            ['ci' => 'V-45678901', 'nombre' => 'Ana Martínez', 'telefono' => '0412-4567890'],
            ['ci' => 'V-56789012', 'nombre' => 'Luis Hernández', 'telefono' => '0416-5678901'],
            ['ci' => 'V-67890123', 'nombre' => 'Carmen Pérez', 'telefono' => '0412-6789012'],
            ['ci' => 'V-78901234', 'nombre' => 'Pedro Gómez', 'telefono' => '0414-7890123'],
            ['ci' => 'V-89012345', 'nombre' => 'Rosa Díaz', 'telefono' => '0416-8901234'],
            ['ci' => 'V-90123456', 'nombre' => 'Jorge Silva', 'telefono' => '0412-9012345'],
            ['ci' => 'V-11223344', 'nombre' => 'Elena Rojas', 'telefono' => '0414-1122334'],
            ['ci' => 'V-22334455', 'nombre' => 'Fernando Castillo', 'telefono' => '0424-2233445'],
            ['ci' => 'V-33445566', 'nombre' => 'Isabel Acosta', 'telefono' => '0412-3344556'],
            // Empresas y comercios (mayor)
            ['ci' => 'J-40000000-1', 'nombre' => 'Abasto La Esperanza C.A.', 'telefono' => '0212-4000001'],
            ['ci' => 'J-40123456-7', 'nombre' => 'Distribuidora El Sol C.A.', 'telefono' => '0212-4012345'],
            ['ci' => 'J-40987654-3', 'nombre' => 'Comercial Los Andes C.A.', 'telefono' => '0212-4098765'],
            ['ci' => 'J-40654321-0', 'nombre' => 'Bodegón El Manantial S.R.L.', 'telefono' => '0212-4065432'],
            ['ci' => 'J-40246810-5', 'nombre' => 'Mercal Express C.A.', 'telefono' => '0212-4024681'],
            ['ci' => 'J-40513579-2', 'nombre' => 'Supermercado Mi Tierrita C.A.', 'telefono' => '0212-4051357'],
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
