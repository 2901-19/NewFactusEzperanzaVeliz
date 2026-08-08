<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->decimal('stock_actual', 14, 4)->default(0)->after('imagen');
            $table->boolean('controla_inventario')->default(true)->after('stock_actual');
            $table->string('unidad_medida')->default('unidad')->after('controla_inventario');
        });

        DB::table('productos')->update([
            'stock_actual' => DB::raw('(COALESCE(stock_paquetes, 0) * COALESCE(unidades_por_paquete, 1)) + COALESCE(stock_unidades, 0)'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['stock_actual', 'controla_inventario', 'unidad_medida']);
        });
    }
};
