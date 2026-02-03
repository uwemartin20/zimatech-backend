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
        Schema::table('processes', function (Blueprint $table) {
            $table->unsignedBigInteger('position_id')->nullable()->after('project_id');
        });

        DB::statement("
            UPDATE processes
            SET position_id = NULL
            WHERE position_id NOT IN (SELECT id FROM positions)
        ");

        Schema::table('processes', function (Blueprint $table) {
            $table
                ->foreign('position_id')
                ->references('id')
                ->on('positions')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processes', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
            $table->dropColumn('position_id');
        });
    }
};
