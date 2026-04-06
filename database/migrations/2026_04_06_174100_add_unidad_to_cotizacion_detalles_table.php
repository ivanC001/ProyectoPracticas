<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('cotizacion_detalles', 'unidad')) {
            return;
        }

        Schema::table('cotizacion_detalles', function (Blueprint $table) {
            $table->string('unidad', 20)->nullable()->after('nombre_item');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('cotizacion_detalles', 'unidad')) {
            return;
        }

        Schema::table('cotizacion_detalles', function (Blueprint $table) {
            $table->dropColumn('unidad');
        });
    }
};
