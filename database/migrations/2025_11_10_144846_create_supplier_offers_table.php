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
        Schema::create('supplier_offers', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->decimal('price', 12, 2)->nullable();
            $table->foreignId('parent_offer_id')->nullable()->constrained('supplier_offers')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('bauteil_id')->constrained('bauteile')->cascadeOnDelete();
            $table->string('offer_number')->nullable();
            $table->text('description')->nullable();
            $table->string('duration')->nullable();
            $table->integer('pieces_to_develop')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_offers');
    }
};
