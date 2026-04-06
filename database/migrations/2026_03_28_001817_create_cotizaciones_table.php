<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {

            $table->id();

            // 🔹 Cliente
            $table->foreignId('cliente_id')
                  ->constrained('clientes');

            // 🔥 Datos tipo tu PDF
            $table->string('asunto'); // CAMBIO DE SELLOS...
            $table->date('fecha');

            $table->text('descripcion_general')->nullable();

            // 🔹 Notas
            $table->text('notas')->nullable();

            // 💰 Totales
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('igv', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // 🔹 Estado
            $table->enum('estado', ['borrador','aprobado','rechazado'])
                  ->default('borrador');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};