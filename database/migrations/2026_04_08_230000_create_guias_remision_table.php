<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guias_remision', function (Blueprint $table) {
            $table->id();

            $table->string('tipo_documento', 2); // 09 remitente | 31 transportista
            $table->string('serie', 4);
            $table->integer('correlativo');
            $table->string('numero_guia');

            $table->dateTime('fecha_emision');
            $table->date('fecha_traslado');

            $table->string('motivo_traslado_codigo', 2)->default('01');
            $table->string('motivo_traslado_descripcion', 255);
            $table->string('modalidad_transporte', 2)->default('02'); // 01 publico | 02 privado
            $table->decimal('peso_total', 12, 3)->default(0);
            $table->string('unidad_peso', 3)->default('KGM');
            $table->integer('numero_bultos')->nullable();
            $table->text('observacion')->nullable();

            $table->string('destinatario_tipo_doc', 2)->default('6');
            $table->string('destinatario_num_doc', 20);
            $table->string('destinatario_razon_social', 255);

            $table->string('partida_ubigeo', 6)->nullable();
            $table->string('partida_direccion', 255);
            $table->string('llegada_ubigeo', 6)->nullable();
            $table->string('llegada_direccion', 255);

            $table->string('transportista_tipo_doc', 2)->nullable();
            $table->string('transportista_num_doc', 20)->nullable();
            $table->string('transportista_razon_social', 255)->nullable();
            $table->string('transportista_reg_mtc', 30)->nullable();

            $table->string('conductor_tipo_doc', 2)->nullable();
            $table->string('conductor_num_doc', 20)->nullable();
            $table->string('conductor_nombres', 255)->nullable();
            $table->string('conductor_licencia', 40)->nullable();

            $table->string('vehiculo_placa', 20)->nullable();
            $table->string('vehiculo_secundario_placa', 20)->nullable();

            $table->unsignedBigInteger('venta_id')->nullable();

            $table->boolean('sunat_enviado')->default(false);
            $table->timestamp('fecha_envio_sunat')->nullable();

            $table->enum('estado_envio', [
                'pendiente',
                'procesando',
                'aceptado',
                'rechazado',
                'error',
            ])->default('pendiente');

            $table->string('codigo_respuesta_sunat')->nullable();
            $table->text('descripcion_respuesta_sunat')->nullable();
            $table->text('mensaje_error')->nullable();

            $table->string('hash_cpe')->nullable();
            $table->string('archivo_xml')->nullable();
            $table->string('archivo_pdf')->nullable();
            $table->string('archivo_cdr')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique('numero_guia');
            $table->unique(['serie', 'correlativo']);
            $table->index(['tipo_documento', 'serie']);
            $table->index(['estado_envio', 'sunat_enviado']);
            $table->index('destinatario_num_doc');
            $table->foreign('venta_id')->references('id')->on('ventas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guias_remision');
    }
};

