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
        Schema::create('offer_calculation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_calculation_id')->constrained('offer_calculations')->cascadeOnDelete();
            $table->foreignId('project_service_id')->constrained('project_services')->cascadeOnDelete();
            $table->decimal('hours', 10, 2)->nullable(); 
            $table->decimal('price_per_hour', 12, 2)->nullable();
            $table->decimal('pieces', 10, 2)->nullable();
            $table->decimal('price_per_unit', 12, 2)->nullable();
            $table->string('comment')->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_calculation_items');
    }
};
