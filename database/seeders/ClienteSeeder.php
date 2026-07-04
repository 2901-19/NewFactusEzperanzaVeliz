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
            ['ci' => 'V-67890123', 'nombre' => 'Diana Silva', 'telefono' => '0424-6789012'],
            ['ci' => 'V-78901234', 'nombre' => 'Pedro Gómez', 'telefono' => '0412-7890123'],
            ['ci' => 'V-89012345', 'nombre' => 'Sofía López', 'telefono' => '0416-8901234'],
            ['ci' => 'V-90123456', 'nombre' => 'Miguel Ángel', 'telefono' => '0424-9012345'],
            ['ci' => 'V-01234567', 'nombre' => 'Laura Castillo', 'telefono' => '0412-0123456'],
            ['ci' => 'E-12345678', 'nombre' => 'Juan Pérez', 'telefono' => '0416-1112233'],
            ['ci' => 'V-11223344', 'nombre' => 'Raúl Giménez', 'telefono' => '0424-4455667'],
            ['ci' => 'V-22334455', 'nombre' => 'Marta Suárez', 'telefono' => '0412-9988776'],
            ['ci' => 'V-33445566', 'nombre' => 'Alberto Rivas', 'telefono' => '0416-1122445'],
            ['ci' => 'V-44556677', 'nombre' => 'Carmen Vega', 'telefono' => '0412-5566778'],
            ['ci' => 'V-55667788', 'nombre' => 'Fernando Mora', 'telefono' => '0424-3344112'],
            ['ci' => 'V-66778899', 'nombre' => 'Patricia León', 'telefono' => '0416-7788990'],
            ['ci' => 'V-77889900', 'nombre' => 'Alejandro Ríos', 'telefono' => '0412-2233445'],
            ['ci' => 'V-88990011', 'nombre' => 'Rosa Medina', 'telefono' => '0424-5566778'],
            ['ci' => 'V-99001122', 'nombre' => 'Diego Vargas', 'telefono' => '0416-9900112'],
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
