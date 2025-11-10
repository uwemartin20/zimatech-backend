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
        Schema::create('supplier_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('supplier_offer_id')->constrained('supplier_offers')->cascadeOnDelete();
            $table->foreignId('project_status_id')->nullable()->constrained('project_statuses')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('checkup_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('extra_note')->nullable();
            $table->decimal('additional_expense', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_projects');
    }
};
