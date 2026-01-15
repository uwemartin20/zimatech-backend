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
        Schema::create('offer_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_offer_id')->constrained()->cascadeOnDelete();
            $table->string('field_name');
            $table->string('field_type')->default('number');
            $table->string('field_value')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_calculations');
    }
};
