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
            $table->foreignId('impuesto_id')->nullable()->after('unidad_medida')->constrained('impuestos')->nullOnDelete();
        });

        $impuestoId = DB::table('impuestos')->orderByDesc('fecha')->orderByDesc('id')->value('id');

        DB::table('productos')->where('tiene_iva', true)->update(['impuesto_id' => $impuestoId]);

        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('tiene_iva');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->boolean('tiene_iva')->default(false)->after('unidad_medida');
        });

        DB::table('productos')->whereNotNull('impuesto_id')->update(['tiene_iva' => true]);

        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['impuesto_id']);
            $table->dropColumn('impuesto_id');
        });
    }
};
