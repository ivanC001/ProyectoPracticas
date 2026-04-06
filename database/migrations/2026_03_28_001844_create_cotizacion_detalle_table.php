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
            $table->foreignId('cotizacion_id')
                ->constrained('cotizaciones')
                ->cascadeOnDelete();
            $table->enum('tipo', ['producto', 'servicio']);
            $table->foreignId('producto_id')
                ->nullable()
                ->constrained('productos')
                ->nullOnDelete();
            $table->foreignId('servicio_id')
                ->nullable()
                ->constrained('servicios')
                ->nullOnDelete();
            $table->string('codigo_item')->nullable();
            $table->string('nombre_item');
            $table->string('unidad', 20)->nullable();
            $table->json('detalle_servicio')->nullable();
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
