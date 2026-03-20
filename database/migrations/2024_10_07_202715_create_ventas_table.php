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
        Schema::create('ventas', function (Blueprint $table) {

            $table->id();

            // Documento
            $table->string('tipo_documento', 2); // 01 factura | 03 boleta
            $table->string('tipo_operacion', 4)->default('0101');

            $table->string('serie', 4); // F001 | B001
            $table->integer('correlativo');

            $table->string('numero_comprobante')->nullable(); // F001-000001

            $table->dateTime('fecha_emision');

            $table->string('moneda', 3)->default('PEN');


            // Cliente
            $table->string('tipo_documento_cliente', 2)->nullable();
            $table->string('numero_documento_cliente', 20)->nullable();
            $table->string('nombre_cliente');


            // Totales
            $table->decimal('total_venta', 12, 2);
            $table->decimal('total_impuestos', 12, 2);


            // Respuesta SUNAT
            $table->string('codigo_respuesta_sunat')->nullable();
            $table->text('descripcion_respuesta_sunat')->nullable();


            // Archivos
            $table->string('hash_cpe')->nullable();
            $table->string('archivo_xml')->nullable();
            $table->string('archivo_pdf')->nullable();
            $table->text('cdr_zip')->nullable();


            // Estado
            $table->string('estado_envio')->default('pendiente');


            $table->timestamps();
            $table->softDeletes();


            // Índices importantes
            $table->unique(['serie','correlativo']);
            $table->index('numero_documento_cliente');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};