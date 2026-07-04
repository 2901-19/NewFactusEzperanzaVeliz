<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            'Alimentos' => 'Arroz, harina, pasta, café',
            'Bebidas' => 'Gaseosas, jugos, agua, maltín',
            'Aseo Personal' => 'Jabón, shampoo, desodorante',
            'Limpieza' => 'Cloro, detergente, desinfectante',
            'Lácteos' => 'Leche, queso, margarina, huevos',
        ];

        foreach ($categorias as $nombre => $desc) {
            Categoria::firstOrCreate(
                ['nombre' => $nombre],
                ['descripcion' => $desc]
            );
        }

        $this->command->info('✓ ' . count($categorias) . ' categorías creadas');
    }
}
