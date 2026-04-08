<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notasCredito', function (Blueprint $table) {

            $table->id();

            // 🔗 relación con venta
            $table->foreignId('venta_id')
                ->constrained('ventas')
                ->cascadeOnDelete();

            // 📄 documento
            $table->string('tipo_documento', 2); // 07 crédito | 08 débito
            $table->string('serie', 4);
            $table->integer('correlativo');

            $table->string('numero_comprobante');
            $table->unique('numero_comprobante');

            $table->dateTime('fecha_emision');

            // 🧾 documento afectado
            $table->string('tipDocAfectado', 2);
            $table->string('numDocAfectado');

            // 📌 motivo SUNAT
            $table->string('codMotivo', 2);
            $table->string('desMotivo');

            // 💰 totales
            $table->decimal('total', 12, 2)->default(0);

            // 🚀 SUNAT
            $table->boolean('sunat_enviado')->default(false);
            $table->timestamp('fecha_envio_sunat')->nullable();

            $table->enum('estado_envio', [
                'pendiente',
                'procesando',
                'aceptado',
                'rechazado',
                'error'
            ])->default('pendiente');

            $table->string('codigo_respuesta_sunat')->nullable();
            $table->text('descripcion_respuesta_sunat')->nullable();
            $table->text('mensaje_error')->nullable();

            // 📂 archivos
            $table->string('hash_cpe')->nullable();
            $table->string('archivo_xml')->nullable();
            $table->string('archivo_pdf')->nullable();
            $table->string('archivo_cdr')->nullable();

            $table->timestamps();

            // ⚡ índices
            $table->unique(['serie','correlativo']);
            $table->index('venta_id');
            $table->index(['estado_envio','sunat_enviado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notasCredito');
    }
};