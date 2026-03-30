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

            // 🔹 Cliente (referencia)
            $table->foreignId('cliente_id')
                  ->constrained('clientes');

            // 🔹 Fechas
            $table->date('fecha');
            $table->date('fecha_vencimiento')->nullable();

            // 🔹 Totales
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('igv', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // 🔹 Estado
            $table->enum('estado', [
                'borrador',
                'enviado',
                'aprobado',
                'rechazado'
            ])->default('borrador');

            // 🔹 Extras
            $table->text('observacion')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};