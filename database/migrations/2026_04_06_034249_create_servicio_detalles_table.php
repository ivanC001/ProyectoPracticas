<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicio_detalles', function (Blueprint $table) {

            $table->id();

            // 🔗 Relación
            $table->foreignId('servicio_id')
                  ->constrained('servicios')
                  ->cascadeOnDelete();

            // 🧾 Paso del servicio
            $table->string('descripcion');

            // 🔢 Orden
            $table->integer('orden')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicio_detalles');
    }
};