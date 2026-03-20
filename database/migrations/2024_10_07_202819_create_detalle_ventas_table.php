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

            $table->id();

            // relación con ventas
            $table->foreignId('venta_id')
                  ->constrained('ventas')
                  ->onDelete('cascade');

            // datos del producto
            $table->string('codigo_producto')->nullable();
            $table->string('descripcion');

            $table->string('unidad',10)->default('NIU');

            $table->decimal('cantidad',10,2);

            $table->decimal('valor_unitario',10,2);

            $table->decimal('igv',10,2);
            $table->decimal('descuentos',10,2);

            $table->decimal('total',10,2);

            $table->timestamps();
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