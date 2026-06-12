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
        Schema::create('printer_problem_emails', function (Blueprint $table) {
            $table->id();

            $table->foreignId('problem_id')
                ->constrained('printer_problems')
                ->cascadeOnDelete();

            $table->enum('email_type', [
                'ai_draft',
                'user_edited',
                'manufacturer_reply',
            ]);

            $table->string('subject')->nullable();

            $table->longText('body');
            $table->enum('direction', ['outgoing', 'incoming'])->default('outgoing');

            $table->boolean('ai_generated')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printer_problem_emails');
    }
};
