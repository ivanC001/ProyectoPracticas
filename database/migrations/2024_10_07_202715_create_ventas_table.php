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
            $table->id(); // Clave primaria
            $table->string('tipo_documento'); // Ejemplo: Factura, Boleta
            $table->string('serie')->default('F001'); // Serie del documento
            $table->string('correlativo'); // Correlativo del documento
            $table->date('fecha_emision'); // Fecha en que se emitió la venta
            $table->string('moneda')->default('PEN'); // Código de la moneda (PEN, USD, etc.)
        
            // Datos del cliente
            $table->string('tipo_documento_cliente'); // Tipo de documento del cliente (DNI, RUC, etc.)
            $table->string('numero_documento_cliente'); // Número de documento del cliente
            $table->string('nombre_cliente'); // Nombre o razón social del cliente
        
            // Totales
            $table->decimal('total_venta', 10, 2); // Total de la venta, incluyendo impuestos
            $table->decimal('total_impuestos', 10, 2); // Total de impuestos (IGV)
        
            // Campos adicionales
            $table->string('hash_cpe')->nullable(); // Hash generado por SUNAT
            $table->string('archivo_xml')->nullable(); // Ruta del archivo XML, si deseas guardarlo
            $table->string('archivo_pdf')->nullable(); // Ruta del archivo PDF generado, si lo necesitas
            $table->string('estado_envio')->default('pendiente'); // Estado del envío: pendiente, aceptado, rechazado
            
            $table->timestamps(); // Campos para created_at y updated_at
            $table->softDeletes(); // deleted_at
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
