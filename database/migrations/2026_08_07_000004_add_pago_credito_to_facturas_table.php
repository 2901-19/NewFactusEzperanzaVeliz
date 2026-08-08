<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->decimal('pago_bs', 14, 2)->nullable()->after('total_usd');
            $table->timestamp('fecha_pago')->nullable()->after('pago_bs');
        });
    }

    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropColumn(['pago_bs', 'fecha_pago']);
        });
    }
};
