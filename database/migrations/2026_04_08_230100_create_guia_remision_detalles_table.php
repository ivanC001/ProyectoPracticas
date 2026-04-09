<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guia_remision_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guia_remision_id');

            $table->string('tipo_item', 20)->nullable(); // producto | servicio
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('codigo', 50)->nullable();
            $table->string('descripcion', 500);
            $table->string('unidad', 3)->default('NIU');
            $table->decimal('cantidad', 12, 3)->default(1);

            $table->timestamps();

            $table->foreign('guia_remision_id')
                ->references('id')
                ->on('guias_remision')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guia_remision_detalles');
    }
};

