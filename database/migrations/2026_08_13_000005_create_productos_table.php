<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('imagen')->nullable();
            $table->decimal('stock_actual', 14, 4)->default(0);
            $table->string('unidad_medida')->default('unidad');
            $table->foreignId('impuesto_id')->nullable()->constrained('impuestos')->nullOnDelete();
            $table->decimal('costo_usd', 12, 2)->default(0);
            $table->string('fuente_tasa')->default('promedio');
            $table->string('estado')->default('disponible');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
