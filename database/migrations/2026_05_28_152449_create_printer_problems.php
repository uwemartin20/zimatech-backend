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
        Schema::create('printer_problems', function (Blueprint $table) {
            $table->id();

            $table->string('problem_uid')->unique();

            // Project info
            $table->string('order_number');
            $table->string('designation');
            $table->string('version_number');

            // Machine settings
            $table->string('design_nozzle_diameter')->nullable();
            $table->string('tool_nozzle_diameter')->nullable();

            $table->string('material')->nullable();

            $table->decimal('print_temperature', 8, 2)->nullable();
            $table->decimal('bed_temperature', 8, 2)->nullable();

            $table->string('nozzle_height')->nullable();

            $table->decimal('offset_x', 8, 3)->nullable();
            $table->decimal('offset_y', 8, 3)->nullable();
            $table->decimal('offset_z', 8, 3)->nullable();

            $table->boolean('maintenance_completed')->default(false);

            // Error info
            $table->string('machine_error_id')->nullable();

            $table->string('short_description');
            $table->text('operator_explanation');

            // AI
            $table->string('issue_type')->nullable();
            $table->longText('ai_troubleshooting')->nullable();
            $table->longText('ai_next_steps')->nullable();

            // Status
            $table->enum('status', ['open', 'closed'])->default('open');

            $table->foreignId('created_by')->constrained('users');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printer_problems');
    }
};
