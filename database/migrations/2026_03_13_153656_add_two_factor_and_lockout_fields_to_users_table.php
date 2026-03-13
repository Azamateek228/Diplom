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
        Schema::table('users', function (Blueprint $table) {
            // Поля для двухфакторной аутентификации
            $table->boolean('two_factor_enabled')->default(false)->after('remember_token');
            $table->string('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            
            // Поля для блокировки аккаунта
            $table->boolean('is_locked')->default(false)->after('two_factor_recovery_codes');
            $table->timestamp('locked_at')->nullable()->after('is_locked');
            $table->timestamp('lock_expires_at')->nullable()->after('locked_at');
            $table->unsignedTinyInteger('failed_login_attempts')->default(0)->after('lock_expires_at');
            $table->string('lock_reason')->nullable()->after('failed_login_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_enabled',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'is_locked',
                'locked_at',
                'lock_expires_at',
                'failed_login_attempts',
                'lock_reason',
            ]);
        });
    }
};
