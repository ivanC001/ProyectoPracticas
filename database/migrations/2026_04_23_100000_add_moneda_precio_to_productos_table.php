<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'moneda_precio')) {
                $table->string('moneda_precio', 3)->default('PEN')->after('precio');
            }
        });

        DB::table('productos')
            ->whereNull('moneda_precio')
            ->update(['moneda_precio' => 'PEN']);
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'moneda_precio')) {
                $table->dropColumn('moneda_precio');
            }
        });
    }
};

