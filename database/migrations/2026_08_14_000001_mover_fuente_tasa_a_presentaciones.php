<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('producto_presentaciones', function (Blueprint $table) {
            $table->string('fuente_tasa')->default('promedio')->after('margen');
        });

        DB::table('productos')->orderBy('id')->each(function ($producto) {
            DB::table('producto_presentaciones')
                ->where('producto_id', $producto->id)
                ->update(['fuente_tasa' => $producto->fuente_tasa]);
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('fuente_tasa');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->string('fuente_tasa')->default('promedio');
        });

        DB::table('productos')->orderBy('id')->each(function ($producto) {
            $tasa = DB::table('producto_presentaciones')
                ->where('producto_id', $producto->id)
                ->orderByRaw('CASE WHEN factor_conversion = 1 THEN 0 ELSE 1 END')
                ->orderBy('id')
                ->value('fuente_tasa');

            DB::table('productos')->where('id', $producto->id)->update(['fuente_tasa' => $tasa ?? 'promedio']);
        });

        Schema::table('producto_presentaciones', function (Blueprint $table) {
            $table->dropColumn('fuente_tasa');
        });
    }
};
