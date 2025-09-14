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
        Schema::create('rutas', function (Blueprint $table) {
    
            $table->id();

            // datos del viaje
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('origen');
            $table->string('destino');

            // responsable del viaje
            $table->foreignId('conductor_id')->constrained('conductores')->onDelete('cascade'); 
            $table->foreignId('camion_id')->constrained('camiones')->onDelete('cascade'); 

            // gastos del viaje
            $table->decimal('caja_chica', 10, 2)->nullable();  // dinero usado
            $table->string('estado')->default('pendiente');    // pendiente, en curso, finalizado
            $table->decimal('pago_viaje', 10, 2)->nullable();  // cuánto se pagó por el viaje
            $table->decimal('ganancia_viaje', 10, 2)->nullable(); // utilidad del viaje
            $table->text('observaciones')->nullable();         // notas adicionales

            $table->timestamps(); 
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        Schema::dropIfExists('rutas');
    }
};
