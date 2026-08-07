<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasa_cambios', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('origen')->default('manual')->after('activo');
        });

        Schema::table('tasa_cambios', function (Blueprint $table) {
            $table->index(['tipo', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::table('tasa_cambios', function (Blueprint $table) {
            $table->dropIndex(['tipo', 'fecha']);
        });

        Schema::table('tasa_cambios', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'origen']);
        });
    }
};
