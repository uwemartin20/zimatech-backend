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
        Schema::table('offer_calculations', function (Blueprint $table) {
            // 🗑️ Remove old columns if they exist
            $table->dropColumn([
                'field_name',
                'field_type',
                'field_value',
            ]);
            $table->string('designation')->nullable();
            $table->decimal('hours', 10, 2)->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->decimal('material_cost', 12, 2)->nullable();
            $table->decimal('external_cost', 12, 2)->nullable();
            $table->integer('pieces')->nullable();
            $table->decimal('total_cost', 14, 2)->nullable();
            $table->decimal('offer_cost', 14, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_calculations', function (Blueprint $table) {
            $table->string('field_name')->nullable();
            $table->string('field_type')->nullable();
            $table->string('field_value')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            // Drop new columns
            $table->dropColumn([
                'designation',
                'hours',
                'cost',
                'material_cost',
                'external_cost',
                'pieces',
                'total_cost',
                'offer_cost',
                'notes'
            ]);
        });
    }
};
