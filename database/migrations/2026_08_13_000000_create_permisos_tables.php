<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('permiso_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permiso_id')->constrained('permisos')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['user_id', 'permiso_id']);
        });

        Schema::create('permiso_rol', function (Blueprint $table) {
            $table->string('rol');
            $table->foreignId('permiso_id')->constrained('permisos')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['rol', 'permiso_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permiso_rol');
        Schema::dropIfExists('permiso_user');
        Schema::dropIfExists('permisos');
    }
};
