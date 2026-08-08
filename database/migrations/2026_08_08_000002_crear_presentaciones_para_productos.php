<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $productos = DB::table('productos')->get(['id', 'costo_usd', 'margen_detal', 'margen_mayor', 'precio_unitario_usd', 'precio_mayor_usd']);

        foreach ($productos as $p) {
            $now = now();

            DB::table('producto_presentaciones')->insert([
                'producto_id' => $p->id,
                'nombre' => 'Unidad',
                'factor_conversion' => 1,
                'margen' => (float) $p->margen_detal,
                'precio_usd' => (float) $p->precio_unitario_usd,
                'activa' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ((float) $p->precio_mayor_usd > 0) {
                DB::table('producto_presentaciones')->insert([
                    'producto_id' => $p->id,
                    'nombre' => 'Mayor',
                    'factor_conversion' => 1,
                    'margen' => (float) $p->margen_mayor,
                    'precio_usd' => (float) $p->precio_mayor_usd,
                    'activa' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('producto_presentaciones')->truncate();
    }
};
