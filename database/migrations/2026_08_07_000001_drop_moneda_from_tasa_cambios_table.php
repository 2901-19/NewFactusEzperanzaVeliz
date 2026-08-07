<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasa_cambios', function (Blueprint $table) {
            $table->dropColumn('moneda');
        });
    }

    public function down(): void
    {
        Schema::table('tasa_cambios', function (Blueprint $table) {
            $table->string('moneda')->default('USD')->after('nombre');
        });
    }
};