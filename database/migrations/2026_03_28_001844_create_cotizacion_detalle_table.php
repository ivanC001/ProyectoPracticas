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

            // 🔹 Relación principal
            $table->foreignId('cotizacion_id')
                  ->constrained('cotizaciones')
                  ->cascadeOnDelete();

            // 🔹 Tipo de item
            $table->enum('tipo', ['producto','servicio']);

            // 🔹 Relaciones opcionales
            $table->foreignId('producto_id')
                  ->nullable()
                  ->constrained('productos')
                  ->nullOnDelete();

            $table->foreignId('servicio_id')
                  ->nullable()
                  ->constrained('servicios')
                  ->nullOnDelete();

            // 🔥 HISTÓRICO (CLAVE PARA FACTURACIÓN)
            $table->string('codigo_item');
            $table->string('nombre_item');

            // 🔹 Opcional pero útil
            $table->string('unidad')->nullable(); // unidad, hora, servicio

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