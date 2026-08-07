<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasa_cambios', function (Blueprint $table) {
            $table->string('nombre')->nullable()->after('tipo');
            $table->boolean('activo')->default(true)->after('moneda');
        });

        $nombres = [
            'promedio' => 'Promedio',
            'dolar' => 'Dólar Paralelo',
            'bcv' => 'BCV',
        ];

        foreach ($nombres as $tipo => $nombre) {
            DB::table('tasa_cambios')->where('tipo', $tipo)->update(['nombre' => $nombre]);
        }
    }

    public function down(): void
    {
        Schema::table('tasa_cambios', function (Blueprint $table) {
            $table->dropColumn(['nombre', 'activo']);
        });
    }
};