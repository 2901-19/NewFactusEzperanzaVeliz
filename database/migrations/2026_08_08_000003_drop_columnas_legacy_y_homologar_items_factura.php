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
        Schema::table('items_factura', function (Blueprint $table) {
            $table->foreignId('presentacion_id')->nullable()->after('producto_id')->nullOnDelete();
            $table->string('presentacion_nombre')->nullable()->after('presentacion_id');
            $table->decimal('factor_conversion', 14, 4)->default(1)->after('presentacion_nombre');
        });

        DB::table('items_factura')->update([
            'presentacion_nombre' => DB::raw("CASE WHEN tipo_venta = 'mayor' THEN 'Mayor' ELSE 'Unidad' END"),
        ]);

        DB::statement(<<<'SQL'
            UPDATE items_factura
            SET presentacion_id = (
                SELECT pp.id
                FROM producto_presentaciones pp
                WHERE pp.producto_id = items_factura.producto_id
                  AND pp.nombre = items_factura.presentacion_nombre
                ORDER BY pp.id
                LIMIT 1
            )
        SQL);

        DB::statement(<<<'SQL'
            UPDATE items_factura
            SET factor_conversion = COALESCE((
                SELECT pp.factor_conversion
                FROM producto_presentaciones pp
                WHERE pp.id = items_factura.presentacion_id
                LIMIT 1
            ), 1)
        SQL);

        Schema::table('items_factura', function (Blueprint $table) {
            $table->decimal('cantidad', 14, 4)->change();
            $table->dropColumn('tipo_venta');
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn([
                'unidades_por_paquete',
                'stock_paquetes',
                'stock_unidades',
                'margen_detal',
                'margen_mayor',
                'precio_unitario_usd',
                'precio_mayor_usd',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items_factura', function (Blueprint $table) {
            $table->string('tipo_venta')->default('unitario')->after('cantidad');
        });

        DB::table('items_factura')->update([
            'tipo_venta' => DB::raw("CASE WHEN COALESCE(presentacion_nombre, 'Unidad') = 'Mayor' THEN 'mayor' ELSE 'unitario' END"),
        ]);

        Schema::table('items_factura', function (Blueprint $table) {
            $table->dropColumn(['presentacion_id', 'presentacion_nombre', 'factor_conversion']);
            $table->integer('cantidad')->change();
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->integer('unidades_por_paquete')->default(1);
            $table->integer('stock_paquetes')->default(0);
            $table->integer('stock_unidades')->default(0);
            $table->decimal('margen_detal', 5, 2)->default(0);
            $table->decimal('margen_mayor', 5, 2)->default(0);
            $table->decimal('precio_unitario_usd', 12, 2)->default(0);
            $table->decimal('precio_mayor_usd', 12, 2)->default(0);
        });
    }
};
