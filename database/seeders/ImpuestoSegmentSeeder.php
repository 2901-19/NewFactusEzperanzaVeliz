<?php

namespace Database\Seeders;

use App\Models\Impuesto;
use Illuminate\Database\Seeder;

class ImpuestoSegmentSeeder extends Seeder
{
    public function run(): void
    {
        Impuesto::create([
            'nombre' => 'IVA General',
            'porcentaje' => 16.00,
            'fecha' => now()->toDateString(),
        ]);

        $this->command->info('✓ IVA 16% registrado');
    }
}
