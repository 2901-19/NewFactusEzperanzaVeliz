<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 50)->unique();
            $table->string('valor', 255);
            $table->timestamps();
        });

        DB::table('configuraciones')->insert([
            ['clave' => 'nombre_negocio', 'valor' => 'Factus Esperanza Veliz'],
            ['clave' => 'rif', 'valor' => 'J-12345678-9'],
            ['clave' => 'direccion', 'valor' => 'Av. Principal, Local 1'],
            ['clave' => 'telefono', 'valor' => '0412-1234567'],
            ['clave' => 'iva_porcentaje', 'valor' => '16'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};
