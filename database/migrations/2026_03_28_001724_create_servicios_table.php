<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();

            // 🔹 Básico
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->decimal('costo', 10, 2)->nullable();
            $table->integer('duracion_estimada')->nullable(); // minutos

            // 🔹 Recursos
            $table->boolean('requiere_personal')->default(false);
            $table->integer('cantidad_personal')->nullable();
            $table->boolean('requiere_equipo')->default(false);
            $table->text('equipos_descripcion')->nullable();

            // 🔹 Ubicación
            $table->string('tipo_servicio')->nullable(); // domicilio, local, remoto
            $table->boolean('requiere_transporte')->default(false);

            // 🔹 Comercial
            $table->text('condiciones')->nullable();
            $table->text('requisitos_cliente')->nullable();
            $table->integer('garantia_dias')->nullable();

            // 🔹 Clasificación
            $table->string('nivel_servicio')->nullable(); // basico, estandar, premium
            $table->string('prioridad')->nullable(); // baja, media, alta

            // 🔹 Otros
            $table->text('instrucciones')->nullable();
            $table->text('observaciones_internas')->nullable();
            $table->string('frecuencia')->nullable(); // unico, recurrente
            $table->string('recurrente_cada')->nullable(); // semanal, mensual

            // 🔹 Estado
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};