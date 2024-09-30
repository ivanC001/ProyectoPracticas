<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCombustiblesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('combustibles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('ruta_id')->unsigned(); // Relación con la tabla rutas
            $table->string('num_factura', 255)->unique(); // Número de factura, debe ser único
            $table->string('grifo', 255); // Lugar donde se compró el combustible
            $table->dateTime('fecha_hora'); // Fecha y hora de la compra de combustible
            $table->decimal('galonesCombustible', 8, 2); // Cantidad de galones
            $table->decimal('importe', 10, 2); // Costo total del combustible
            $table->integer('kilometraje_inicial')->nullable(); // Kilometraje inicial (opcional)
            $table->integer('kilometraje_final')->nullable(); // Kilometraje final (opcional)
            $table->string('tipo_combustible', 50)->nullable(); // Tipo de combustible (opcional)
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at

            // Llave foránea
            $table->foreign('ruta_id')->references('id')->on('rutas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('combustibles');
    }
}
