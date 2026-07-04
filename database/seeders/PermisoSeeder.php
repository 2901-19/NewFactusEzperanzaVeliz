<?php

namespace Database\Seeders;

use App\Models\Permiso;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
        ];

        $inserted = [];
        foreach ($permisos as $p) {
            $permiso = Permiso::firstOrCreate(
                ['slug' => $p['slug']],
                $p
            );
            $inserted[$permiso->slug] = $permiso->id;
        }

        $adminPermisos = array_values($inserted);
        $cajeroPermisos = array_intersect_key($inserted, array_flip([
            'ver-dashboard',
            'usar-pos',
            'gestionar-clientes',
            'ver-facturas',
            'crear-facturas',
            'anular-facturas',
            'gestionar-creditos',
        ]));

        $now = now();
        $rows = [];
        foreach ($adminPermisos as $permisoId) {
            $rows[] = ['rol' => 'admin', 'permiso_id' => $permisoId, 'created_at' => $now, 'updated_at' => $now];
        }
        foreach ($cajeroPermisos as $permisoId) {
            $rows[] = ['rol' => 'cajero', 'permiso_id' => $permisoId, 'created_at' => $now, 'updated_at' => $now];
        }

        DB::table('permiso_rol')->upsert($rows, ['rol', 'permiso_id'], ['updated_at']);
    }
}
