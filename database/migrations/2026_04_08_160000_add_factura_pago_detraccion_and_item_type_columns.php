<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (!Schema::hasColumn('ventas', 'forma_pago')) {
                $table->string('forma_pago', 10)->default('contado')->after('moneda');
            }

            if (!Schema::hasColumn('ventas', 'credito_total_cuotas')) {
                $table->unsignedInteger('credito_total_cuotas')->nullable()->after('forma_pago');
            }

            if (!Schema::hasColumn('ventas', 'credito_monto_pendiente')) {
                $table->decimal('credito_monto_pendiente', 12, 2)->nullable()->after('credito_total_cuotas');
            }

            if (!Schema::hasColumn('ventas', 'credito_fecha_vencimiento')) {
                $table->date('credito_fecha_vencimiento')->nullable()->after('credito_monto_pendiente');
            }

            if (!Schema::hasColumn('ventas', 'detraccion_aplica')) {
                $table->boolean('detraccion_aplica')->default(false)->after('credito_fecha_vencimiento');
            }

            if (!Schema::hasColumn('ventas', 'detraccion_codigo')) {
                $table->string('detraccion_codigo', 3)->nullable()->after('detraccion_aplica');
            }

            if (!Schema::hasColumn('ventas', 'detraccion_porcentaje')) {
                $table->decimal('detraccion_porcentaje', 5, 2)->nullable()->after('detraccion_codigo');
            }

            if (!Schema::hasColumn('ventas', 'detraccion_monto')) {
                $table->decimal('detraccion_monto', 12, 2)->nullable()->after('detraccion_porcentaje');
            }

            if (!Schema::hasColumn('ventas', 'detraccion_cuenta')) {
                $table->string('detraccion_cuenta', 30)->nullable()->after('detraccion_monto');
            }

            if (!Schema::hasColumn('ventas', 'detraccion_medio_pago')) {
                $table->string('detraccion_medio_pago', 3)->nullable()->after('detraccion_cuenta');
            }

            if (!Schema::hasColumn('ventas', 'observacion')) {
                $table->text('observacion')->nullable()->after('detraccion_medio_pago');
            }
        });

        Schema::table('detalle_ventas', function (Blueprint $table) {
            if (!Schema::hasColumn('detalle_ventas', 'tipo_item')) {
                $table->string('tipo_item', 15)->default('producto')->after('venta_id');
            }

            if (!Schema::hasColumn('detalle_ventas', 'item_id')) {
                $table->unsignedBigInteger('item_id')->nullable()->after('tipo_item');
            }
        });
    }

    public function down(): void
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            if (Schema::hasColumn('detalle_ventas', 'item_id')) {
                $table->dropColumn('item_id');
            }

            if (Schema::hasColumn('detalle_ventas', 'tipo_item')) {
                $table->dropColumn('tipo_item');
            }
        });

        Schema::table('ventas', function (Blueprint $table) {
            $columns = [
                'forma_pago',
                'credito_total_cuotas',
                'credito_monto_pendiente',
                'credito_fecha_vencimiento',
                'detraccion_aplica',
                'detraccion_codigo',
                'detraccion_porcentaje',
                'detraccion_monto',
                'detraccion_cuenta',
                'detraccion_medio_pago',
                'observacion',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('ventas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
