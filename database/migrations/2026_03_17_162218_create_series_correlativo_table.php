<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('series_correlativo', function (Blueprint $table) {

            $table->id();

            $table->string('tipo_documento', 2); // 01,03,07,08,09
            $table->string('serie', 4);

            $table->integer('correlativo_actual')->default(0);
            $table->boolean('activo')->default(true);

            $table->timestamps();

            // 🔥 CRÍTICO: evita duplicados
            $table->unique(['tipo_documento','serie']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('series_correlativo');
    }
};