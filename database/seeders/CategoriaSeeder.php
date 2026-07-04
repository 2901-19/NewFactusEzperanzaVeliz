<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            'Alimentos' => 'Arroz, harina, pasta, azúcar, café, aceite',
            'Bebidas' => 'Gaseosas, jugos, agua, maltín',
            'Licores' => 'Ron, cerveza, whisky, vino',
            'Aseo Personal' => 'Jabón, shampoo, desodorante, pasta dental',
            'Limpieza' => 'Cloro, detergente, desinfectante, lavaplatos',
            'Mascotas' => 'Comida para perros y gatos',
            'Golosinas' => 'Chocolate, papas, chicles',
            'Electrónicos' => 'Pilas, bombillos, cables',
            'Lácteos' => 'Leche, queso, margarina, huevos',
            'Carnes y Embutidos' => 'Jamón, salchicha, mortadela',
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
