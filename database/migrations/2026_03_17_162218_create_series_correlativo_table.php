<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('series_correlativo', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_documento', 2); // 01 factura | 03 boleta
            $table->string('serie', 4);          // F001 | B001
            $table->integer('correlativo_actual')->default(0);

            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series_correlativo');
    }
};
