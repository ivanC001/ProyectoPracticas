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
        Schema::create('productos', function (Blueprint $table) {

            $table->id();

            // identificación
            $table->string('codigo')->unique();
            $table->string('descripcion');

            // clasificación
            $table->string('categoria')->nullable();

            // unidad SUNAT
            $table->string('unidad',10)->default('NIU');

            // precios
            $table->decimal('precio',10,2);

            // inventario
            $table->integer('stock')->default(0);

            // estado
            $table->boolean('activo')->default(true);

            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};