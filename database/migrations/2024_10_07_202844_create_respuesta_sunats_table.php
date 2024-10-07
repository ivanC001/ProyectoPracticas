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
        Schema::create('respuestas_sunat', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->unsignedBigInteger('venta_id'); // Llave foránea con la tabla ventas
            $table->string('codigo_respuesta'); // Código de respuesta de SUNAT (ej: 0 para aceptado)
            $table->string('mensaje_respuesta'); // Mensaje detallado de la respuesta
            $table->string('ticket_consulta')->nullable(); // Ticket de consulta, si aplica (ej. para documentos pendientes)
            $table->json('respuesta_completa')->nullable(); // Almacena la respuesta completa en formato JSON si es necesario
        
            $table->timestamps(); // Campos para created_at y updated_at
            $table->softDeletes(); // deleted_at
            // Relación con la tabla ventas
            $table->foreign('venta_id')->references('id')->on('ventas')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('respuestas_sunat');
    }
};
