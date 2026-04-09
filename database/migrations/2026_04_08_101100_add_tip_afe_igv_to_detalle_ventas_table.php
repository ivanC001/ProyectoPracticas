<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            if (!Schema::hasColumn('detalle_ventas', 'tip_afe_igv')) {
                $table->string('tip_afe_igv', 2)->default('10')->after('unidad');
            }
        });
    }

    public function down(): void
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            if (Schema::hasColumn('detalle_ventas', 'tip_afe_igv')) {
                $table->dropColumn('tip_afe_igv');
            }
        });
    }
};
