<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizacion_detalles', function (Blueprint $table) {

            $table->id();

            // 🔗 Relación principal
            $table->foreignId('cotizacion_id')
                  ->constrained('cotizaciones')
                  ->cascadeOnDelete();

            // 🔹 Tipo
            $table->enum('tipo', ['producto','servicio']);

            // 🔹 Relaciones opcionales
            $table->foreignId('producto_id')->nullable()
                  ->constrained('productos')
                  ->nullOnDelete();

            $table->foreignId('servicio_id')->nullable()
                  ->constrained('servicios')
                  ->nullOnDelete();

            // 🔥 SNAPSHOT
            $table->string('codigo_item')->nullable();
            $table->string('nombre_item');

            // 🔥 AQUÍ ESTÁ EL JSON
            $table->json('detalle_servicio')->nullable();

            // 💰 Datos económicos
            $table->decimal('cantidad', 10, 2);
            $table->decimal('precio', 12, 2);
            $table->decimal('subtotal', 12, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizacion_detalles');
    }
};