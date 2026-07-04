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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('imagen')->nullable();
            $table->integer('stock')->default(0);
            $table->integer('paquetes')->default(0);
            $table->integer('unidades_paquete')->default(1);
            $table->decimal('precio_costo_usd', 12, 2)->default(0);
            $table->decimal('porcentaje_ganancia', 5, 2)->default(0);
            $table->decimal('precio_venta_usd', 12, 2)->default(0);
            $table->boolean('tiene_iva')->default(true);
            $table->string('estado')->default('disponible');
            $table->string('fuente_tasa')->default('promedio');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
