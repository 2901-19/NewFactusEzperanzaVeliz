<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->after('cliente_id');
            $table->index('fecha_venta');
            $table->index('estado');
            $table->index('estado_credito');
        });

        Schema::table('items_factura', function (Blueprint $table) {
            $table->index('factura_id');
        });
    }

    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropIndex(['fecha_venta']);
            $table->dropIndex(['estado']);
            $table->dropIndex(['estado_credito']);
        });

        Schema::table('items_factura', function (Blueprint $table) {
            $table->dropIndex(['factura_id']);
        });
    }
};
