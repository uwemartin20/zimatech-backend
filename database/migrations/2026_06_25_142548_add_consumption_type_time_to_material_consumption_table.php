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
        Schema::table('material_consumption', function (Blueprint $table) {
            $table->enum("consumption_type", ["use", "return"])->default("use")->after("material_id");
            $table->timestamp("consumption_time")->nullable()->after("consumption_type");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_consumption', function (Blueprint $table) {
            $table->dropColumn(['consumption_type', 'consumption_time']);
        });
    }
};