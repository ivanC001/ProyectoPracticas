<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();

            // Tipo de documento SUNAT
            $table->string('tipo_doc', 2); // 1 DNI | 6 RUC | 0 VARIOS

            // Documento único
            $table->string('num_doc')->unique();

            // Nombre o razón social
            $table->string('razon_social');

            // Datos opcionales
            $table->string('direccion')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();

            // Estado (por si luego quieres desactivar clientes)
            $table->boolean('estado')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};