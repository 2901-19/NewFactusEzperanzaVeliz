<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ImpuestoSegmentSeeder::class,
            CategoriaSeeder::class,
            ClienteSeeder::class,
            ProductoSeeder::class,
            TasaCambioSeeder::class,
            FacturaSeeder::class,
        ]);
    }
}
