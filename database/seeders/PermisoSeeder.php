<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Seeder;

class PermisoSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            ['nombre' => 'Ver Dashboard', 'slug' => 'ver-dashboard'],
            ['nombre' => 'Usar POS', 'slug' => 'usar-pos'],
            ['nombre' => 'Gestionar Productos', 'slug' => 'gestionar-productos'],
            ['nombre' => 'Gestionar Clientes', 'slug' => 'gestionar-clientes'],
            ['nombre' => 'Ver Facturas', 'slug' => 'ver-facturas'],
            ['nombre' => 'Crear Facturas', 'slug' => 'crear-facturas'],
            ['nombre' => 'Anular Facturas', 'slug' => 'anular-facturas'],
            ['nombre' => 'Gestionar Créditos', 'slug' => 'gestionar-creditos'],
            ['nombre' => 'Gestionar Impuestos', 'slug' => 'gestionar-impuestos'],
            ['nombre' => 'Gestionar Tasas de Cambio', 'slug' => 'gestionar-tasas'],
            ['nombre' => 'Gestionar Categorías', 'slug' => 'gestionar-categorias'],
            ['nombre' => 'Gestionar Usuarios', 'slug' => 'gestionar-usuarios'],
            ['nombre' => 'Ver Reporte Facturas', 'slug' => 'ver-reporte-facturas'],
            ['nombre' => 'Ver Balance Mensual', 'slug' => 'ver-balance'],
            ['nombre' => 'Ver Stock Bajo', 'slug' => 'ver-stock-bajo'],
            ['nombre' => 'Exportar Datos', 'slug' => 'exportar-datos'],
            ['nombre' => 'Importar Datos', 'slug' => 'importar-datos'],
            ['nombre' => 'Configurar Impresora', 'slug' => 'configurar-impresora'],
            ['nombre' => 'Ver Lista de Precios', 'slug' => 'ver-lista-precios'],
            ['nombre' => 'Configuración del Sistema', 'slug' => 'configuracion'],
            ['nombre' => 'Actualizar Precios', 'slug' => 'actualizar-precios'],
            ['nombre' => 'Actualizar Inventarios', 'slug' => 'actualizar-inventarios'],
        ];

        $permisoIds = [];
        foreach ($permisos as $p) {
            $permiso = Permiso::firstOrCreate(
                ['slug' => $p['slug']],
                $p
            );
            $permisoIds[] = $permiso->id;
        }

        $admin = Rol::firstOrCreate(
            ['slug' => 'admin'],
            [
                'nombre' => 'Administrador',
                'descripcion' => 'Rol protegido con acceso total al sistema.',
                'protegido' => true,
            ]
        );

        $admin->permisos()->sync($permisoIds);
    }
}
