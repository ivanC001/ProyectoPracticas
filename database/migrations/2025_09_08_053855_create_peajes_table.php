<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('peajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruta_id')->constrained('rutas')->onDelete('cascade');
            $table->string('nombre'); // nombre del peaje
            $table->decimal('importe', 10, 2); // monto en soles
            $table->dateTime('fecha_hora'); // fecha y hora del pago
            $table->string('comprobante')->nullable(); // número de boleta/factura
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('peajes');
    }
};
