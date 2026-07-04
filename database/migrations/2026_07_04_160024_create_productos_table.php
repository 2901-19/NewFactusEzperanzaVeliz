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
            $table->integer('unidades_por_paquete')->default(1);
            $table->integer('stock_paquetes')->default(0);
            $table->integer('stock_unidades')->default(0);
            $table->decimal('precio_unitario_usd', 12, 2)->default(0);
            $table->decimal('precio_mayor_usd', 12, 2)->default(0);
            $table->integer('cantidad_minima_mayor')->default(0);
            $table->boolean('tiene_iva')->default(true);
            $table->string('fuente_tasa')->default('promedio');
            $table->string('estado')->default('disponible');
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
