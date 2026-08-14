<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasa_cambios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tipo');
            $table->string('nombre')->nullable();
            $table->decimal('monto', 12, 2);
            $table->date('fecha');
            $table->boolean('activo')->default(true);
            $table->string('origen')->default('manual');
            $table->timestamps();

            $table->index(['tipo', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasa_cambios');
    }
};
