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
        Schema::create('production_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('machine_id')->nullable()->constrained('machines')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->text('notes')->nullable();
            $table->string('type'); // 'shift', 'machine', 'both'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_schedules');
    }
};
