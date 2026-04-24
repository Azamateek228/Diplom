<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('city_id');
            $table->string('payment_qr_url')->nullable()->after('payment_method');
            $table->string('payment_pdf_url')->nullable()->after('payment_qr_url');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_qr_url', 'payment_pdf_url']);
        });
    }
};
