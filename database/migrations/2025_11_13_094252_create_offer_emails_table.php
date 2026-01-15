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
        Schema::create('offer_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_offer_id')->constrained()->cascadeOnDelete();
            $table->string('sender')->nullable();
            $table->string('recipient')->nullable();
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();
            $table->enum('direction', ['inbound', 'outbound']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_emails');
    }
};
