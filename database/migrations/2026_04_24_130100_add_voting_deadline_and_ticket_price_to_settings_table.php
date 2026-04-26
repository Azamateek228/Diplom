<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dateTime('voting_deadline')->nullable()->after('current_city_id');
            $table->unsignedInteger('ticket_price')->default(350)->after('voting_deadline');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['voting_deadline', 'ticket_price']);
        });
    }
};
