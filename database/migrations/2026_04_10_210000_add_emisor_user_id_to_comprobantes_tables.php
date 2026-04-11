<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (!Schema::hasColumn('ventas', 'emisor_user_id')) {
                $table->foreignId('emisor_user_id')
                    ->nullable()
                    ->after('nombre_cliente')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        Schema::table('notasCredito', function (Blueprint $table) {
            if (!Schema::hasColumn('notasCredito', 'emisor_user_id')) {
                $table->foreignId('emisor_user_id')
                    ->nullable()
                    ->after('venta_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        Schema::table('guias_remision', function (Blueprint $table) {
            if (!Schema::hasColumn('guias_remision', 'emisor_user_id')) {
                $table->foreignId('emisor_user_id')
                    ->nullable()
                    ->after('venta_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('guias_remision', function (Blueprint $table) {
            if (Schema::hasColumn('guias_remision', 'emisor_user_id')) {
                $table->dropForeign(['emisor_user_id']);
                $table->dropColumn('emisor_user_id');
            }
        });

        Schema::table('notasCredito', function (Blueprint $table) {
            if (Schema::hasColumn('notasCredito', 'emisor_user_id')) {
                $table->dropForeign(['emisor_user_id']);
                $table->dropColumn('emisor_user_id');
            }
        });

        Schema::table('ventas', function (Blueprint $table) {
            if (Schema::hasColumn('ventas', 'emisor_user_id')) {
                $table->dropForeign(['emisor_user_id']);
                $table->dropColumn('emisor_user_id');
            }
        });
    }
};

