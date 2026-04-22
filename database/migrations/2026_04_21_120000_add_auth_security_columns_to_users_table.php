<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('failed_login_attempts')->default(0)->after('activo');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            $table->string('two_factor_code')->nullable()->after('locked_until');
            $table->timestamp('two_factor_expires_at')->nullable()->after('two_factor_code');
            $table->unsignedTinyInteger('two_factor_attempts')->default(0)->after('two_factor_expires_at');
            $table->boolean('two_factor_verified')->default(false)->after('two_factor_attempts');
            $table->timestamp('last_login_at')->nullable()->after('two_factor_verified');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'failed_login_attempts',
                'locked_until',
                'two_factor_code',
                'two_factor_expires_at',
                'two_factor_attempts',
                'two_factor_verified',
                'last_login_at',
            ]);
        });
    }
};
