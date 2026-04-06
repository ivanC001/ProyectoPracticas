<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conductores', function (Blueprint $table) {
            if (!Schema::hasColumn('conductores', 'camion_id')) {
                $table->foreignId('camion_id')
                    ->nullable()
                    ->after('ciudad')
                    ->constrained('camiones')
                    ->nullOnDelete();
            }
        });

        $camionIds = DB::table('camiones')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->pluck('id')
            ->values();

        if ($camionIds->isEmpty()) {
            return;
        }

        $conductores = DB::table('conductores')
            ->whereNull('deleted_at')
            ->whereNull('camion_id')
            ->orderBy('id')
            ->get(['id']);

        foreach ($conductores as $index => $conductor) {
            DB::table('conductores')
                ->where('id', $conductor->id)
                ->update([
                    'camion_id' => $camionIds[$index % $camionIds->count()],
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('conductores', function (Blueprint $table) {
            if (Schema::hasColumn('conductores', 'camion_id')) {
                $table->dropConstrainedForeignId('camion_id');
            }
        });
    }
};
