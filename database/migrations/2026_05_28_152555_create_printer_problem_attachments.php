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
        Schema::create('printer_problem_attachments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('problem_id')
                ->constrained('printer_problems')
                ->cascadeOnDelete();

            $table->enum('type', ['image', 'pdf']);

            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type');

            $table->unsignedBigInteger('file_size')->nullable();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printer_problem_attachments');
    }
};
