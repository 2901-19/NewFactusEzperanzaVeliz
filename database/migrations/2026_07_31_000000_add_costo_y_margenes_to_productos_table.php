<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->decimal('costo_usd', 12, 2)->default(0)->after('stock_unidades');
            $table->decimal('margen_detal', 5, 2)->default(0)->after('costo_usd');
            $table->decimal('margen_mayor', 5, 2)->default(0)->after('margen_detal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['costo_usd', 'margen_detal', 'margen_mayor']);
        });
    }
};
