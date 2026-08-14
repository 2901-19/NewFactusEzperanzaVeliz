<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items_factura', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('presentacion_id')->nullable()->constrained('producto_presentaciones')->nullOnDelete();
            $table->string('presentacion_nombre')->nullable();
            $table->decimal('factor_conversion', 14, 4)->default(1);
            $table->decimal('cantidad', 14, 4);
            $table->decimal('precio_unitario_usd', 12, 2);
            $table->decimal('precio_unitario_bs', 14, 2);
            $table->decimal('subtotal', 14, 2);
            $table->string('unidad_medida')->nullable();
            $table->timestamps();

            $table->index('factura_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items_factura');
    }
};
