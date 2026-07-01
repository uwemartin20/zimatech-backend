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
        Schema::table('materials', function (Blueprint $table) {
            $table->string('image')->nullable();
            $table->enum('order_status', ['notified', 'ordered', 'blocked', 'delivered'])->nullable();
            $table->foreignId('lager_id')->nullable()->constrained('lager')->onDelete('set null');
            $table->boolean('is_werkzeug')->default(false);
            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material', function (Blueprint $table) {
            $table->dropForeign(['lager_id']);
            $table->dropColumn(['image', 'order_status', 'lager_id']);
        });
    }
};