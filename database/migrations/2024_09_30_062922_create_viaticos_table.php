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
        Schema::create('viaticos', function (Blueprint $table) {
            $table->bigIncrements('id'); // Llave primaria
            $table->bigInteger('ruta_id')->unsigned(); // Relación con la tabla rutas
            $table->string('nombre_servicio', 255);
            $table->date('fecha');
            $table->string('numero_factura', 255);
            $table->decimal('importe', 10, 2);
            $table->text('descripcion')->nullable();
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at

            // Llave foránea
            $table->foreign('ruta_id')->references('id')->on('rutas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('viaticos');
    }
};
