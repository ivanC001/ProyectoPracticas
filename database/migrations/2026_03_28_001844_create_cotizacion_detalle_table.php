<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('cotizacion_detalle', function (Blueprint $table) {
        $table->id();

        $table->foreignId('cotizacion_id')
              ->constrained('cotizaciones')
              ->onDelete('cascade');

        $table->enum('tipo', ['producto','servicio']);

        // 🔥 SOLO UNO SE USA
        $table->foreignId('producto_id')
              ->nullable()
              ->constrained('productos')
              ->nullOnDelete();

        $table->foreignId('servicio_id')
              ->nullable()
              ->constrained('servicios')
              ->nullOnDelete();

        $table->string('descripcion');

        $table->decimal('cantidad', 10, 2);
        $table->decimal('precio', 10, 2);
        $table->decimal('subtotal', 10, 2);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotizacion_detalle');
    }
};
