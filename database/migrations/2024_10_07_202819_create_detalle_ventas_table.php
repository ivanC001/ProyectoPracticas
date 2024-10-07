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
        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->unsignedBigInteger('venta_id'); // Llave foránea con la tabla ventas
            $table->string('codigo_producto'); // Código del producto o servicio
            $table->string('descripcion'); // Descripción del producto o servicio
            $table->integer('cantidad'); // Cantidad vendida
            $table->decimal('precio_unitario', 10, 2); // Precio unitario del producto o servicio
            $table->decimal('subtotal', 10, 2); // Subtotal sin impuestos
            $table->decimal('igv', 10, 2); // Monto de IGV (Impuesto General a las Ventas)
            $table->decimal('total', 10, 2); // Total incluyendo IGV
        
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
        Schema::dropIfExists('detalle_ventas');
    }
};
