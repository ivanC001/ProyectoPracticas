<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasMonedaColumn = Schema::hasColumn('cotizaciones', 'moneda');

        Schema::table('cotizaciones', function (Blueprint $table) use ($hasMonedaColumn) {
            if (!Schema::hasColumn('cotizaciones', 'tipo_cambio')) {
                $column = $table->decimal('tipo_cambio', 10, 4)->nullable();
                if ($hasMonedaColumn) {
                    $column->after('moneda');
                }
            }
        });

        if (Schema::hasColumn('cotizaciones', 'tipo_cambio')) {
            DB::table('cotizaciones')
                ->whereNull('tipo_cambio')
                ->update(['tipo_cambio' => 3.8000]);
        }
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            if (Schema::hasColumn('cotizaciones', 'tipo_cambio')) {
                $table->dropColumn('tipo_cambio');
            }
        });
    }
};
