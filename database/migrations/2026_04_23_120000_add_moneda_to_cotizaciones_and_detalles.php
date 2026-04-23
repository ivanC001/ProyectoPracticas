<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('cotizaciones', 'moneda')) {
                $table->string('moneda', 3)->default('PEN')->after('fecha');
            }
        });

        DB::table('cotizaciones')
            ->whereNull('moneda')
            ->update(['moneda' => 'PEN']);

        Schema::table('cotizacion_detalles', function (Blueprint $table) {
            if (!Schema::hasColumn('cotizacion_detalles', 'moneda_precio')) {
                $table->string('moneda_precio', 3)->default('PEN')->after('precio');
            }
        });

        DB::table('cotizacion_detalles')
            ->whereNull('moneda_precio')
            ->update(['moneda_precio' => 'PEN']);
    }

    public function down(): void
    {
        Schema::table('cotizacion_detalles', function (Blueprint $table) {
            if (Schema::hasColumn('cotizacion_detalles', 'moneda_precio')) {
                $table->dropColumn('moneda_precio');
            }
        });

        Schema::table('cotizaciones', function (Blueprint $table) {
            if (Schema::hasColumn('cotizaciones', 'moneda')) {
                $table->dropColumn('moneda');
            }
        });
    }
};

