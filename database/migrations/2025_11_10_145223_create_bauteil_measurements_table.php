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
        Schema::create('bauteil_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bauteil_id')->constrained('bauteile')->cascadeOnDelete();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('depth', 8, 2)->nullable();
            $table->decimal('thickness', 8, 2)->nullable();
            $table->decimal('radius', 8, 2)->nullable();
            $table->string('unit', 10)->default('mm');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bauteil_measurements');
    }
};
