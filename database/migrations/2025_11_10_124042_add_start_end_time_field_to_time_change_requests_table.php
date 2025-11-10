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
        Schema::table('time_change_requests', function (Blueprint $table) {
            $table->dateTime('record_start_time')->nullable();
            $table->dateTime('record_end_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_change_requests', function (Blueprint $table) {
            //
        });
    }
};
