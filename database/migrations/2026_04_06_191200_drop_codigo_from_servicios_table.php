<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('servicios', 'codigo')) {
            return;
        }

        Schema::table('servicios', function (Blueprint $table) {
            $table->dropColumn('codigo');
        });

        DB::table('cotizacion_detalles')
            ->where('tipo', 'servicio')
            ->update(['codigo_item' => null]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('servicios', 'codigo')) {
            return;
        }

        Schema::table('servicios', function (Blueprint $table) {
            $table->string('codigo')->nullable()->after('id');
        });
    }
};
