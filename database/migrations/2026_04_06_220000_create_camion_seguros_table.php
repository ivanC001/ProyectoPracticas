<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('camion_seguros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camion_id')->constrained('camiones')->cascadeOnDelete();
            $table->string('tipo_seguro', 100);
            $table->string('aseguradora', 150)->nullable();
            $table->string('numero_poliza', 100)->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_vencimiento');
            $table->decimal('monto', 10, 2)->nullable();
            $table->unsignedInteger('alertar_dias_antes')->default(30);
            $table->boolean('activo')->default(true);
            $table->timestamp('ultimo_aviso_enviado_at')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camion_seguros');
    }
};
