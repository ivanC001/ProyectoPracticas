<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guias_remision', function (Blueprint $table) {
            $table->unsignedBigInteger('guia_remitente_id')->nullable()->after('venta_id');
            $table->string('documento_rel_tipo', 2)->nullable()->after('guia_remitente_id');
            $table->string('documento_rel_numero', 50)->nullable()->after('documento_rel_tipo');
            $table->string('documento_rel_emisor', 20)->nullable()->after('documento_rel_numero');

            $table->foreign('guia_remitente_id')
                ->references('id')
                ->on('guias_remision')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('guias_remision', function (Blueprint $table) {
            $table->dropForeign(['guia_remitente_id']);
            $table->dropColumn([
                'guia_remitente_id',
                'documento_rel_tipo',
                'documento_rel_numero',
                'documento_rel_emisor',
            ]);
        });
    }
};

