<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            if (!Schema::hasColumn('servicios', 'moneda_precio')) {
                $table->string('moneda_precio', 3)->default('PEN')->after('precio');
            }
        });

        if (Schema::hasColumn('servicios', 'moneda_precio')) {
            DB::table('servicios')
                ->whereNull('moneda_precio')
                ->update(['moneda_precio' => 'PEN']);
        }
    }

    public function down(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            if (Schema::hasColumn('servicios', 'moneda_precio')) {
                $table->dropColumn('moneda_precio');
            }
        });
    }
};
