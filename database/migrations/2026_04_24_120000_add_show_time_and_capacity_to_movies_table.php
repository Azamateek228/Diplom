<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dateTime('show_time')->nullable()->after('venue');
            $table->unsignedInteger('venue_capacity')->nullable()->after('show_time');
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn(['show_time', 'venue_capacity']);
        });
    }
};
