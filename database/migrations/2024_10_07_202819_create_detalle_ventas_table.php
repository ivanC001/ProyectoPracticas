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
        Schema::create('detalle_ventas', function (Blueprint $table) {

            $table->id();

           
            $table->foreignId('venta_id')
                ->constrained('ventas')
                ->cascadeOnDelete();

            // 📦 Producto
            $table->string('codigo_producto');
            $table->string('descripcion');
            $table->string('unidad', 10)->default('NIU');

            // 💰 Valores SUNAT
            $table->decimal('cantidad', 10, 2);
            $table->decimal('valor_unitario', 10, 2);
            $table->decimal('precio_unitario', 10, 2);

            $table->decimal('descuento', 10, 2)->default(0);

            $table->decimal('subtotal', 10, 2);
            $table->decimal('igv', 10, 2);
            $table->decimal('total', 10, 2);

            $table->timestamps();

            // ⚡ índice
            $table->index('venta_id');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_ventas'); // 🔥 corregido
    }
};