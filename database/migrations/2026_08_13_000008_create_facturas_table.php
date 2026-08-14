<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->string('correlativo')->unique();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('productos');
            $table->decimal('tasa_cambio', 12, 2);
            $table->string('metodo_pago');
            $table->json('detalle_pago')->nullable();
            $table->decimal('subtotal_bs', 14, 2)->default(0);
            $table->decimal('iva_bs', 14, 2)->default(0);
            $table->decimal('total_bs', 14, 2)->default(0);
            $table->decimal('total_usd', 12, 2)->default(0);
            $table->decimal('pago_bs', 14, 2)->nullable();
            $table->timestamp('fecha_pago')->nullable();
            $table->string('estado')->default('contado');
            $table->string('estado_credito')->nullable();
            $table->dateTime('fecha_venta');
            $table->timestamps();

            $table->index('fecha_venta');
            $table->index('estado');
            $table->index('estado_credito');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
