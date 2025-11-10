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
        Schema::table('bauteile', function (Blueprint $table) {
            $table->boolean('is_werkzeug')->default(false);
            $table->boolean('is_baugruppe')->default(false);
            $table->string('image')->nullable();
            $table->boolean('in_house_production')->default(false);
            $table->foreignId('bauteil_measurement_id')->nullable()->constrained('bauteil_measurements')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bauteile', function (Blueprint $table) {
            //
        });
    }
};
