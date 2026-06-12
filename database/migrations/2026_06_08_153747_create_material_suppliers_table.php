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
        Schema::create('material_suppliers', function (Blueprint $table) {
            $table->id();

            // Foreign key for materials table
            $table->foreignId('material_id')
                ->constrained()
                ->onDelete('cascade');

            // Foreign key for suppliers table
            $table->foreignId('supplier_id')
                ->constrained()
                ->onDelete('cascade');

            // Optional: Prevents duplicate combinations of the same material and supplier
            $table->unique(['material_id', 'supplier_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_suppliers');
    }
};
