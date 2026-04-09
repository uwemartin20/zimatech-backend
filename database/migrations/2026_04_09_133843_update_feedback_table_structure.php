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
        Schema::table('feedback', function (Blueprint $table) {

            // Drop old columns
            $table->dropColumn([
                'description',
                'priority',
                'ai_solution',
            ]);

            // Modify type enum
            $table->enum('type', ['maschinen', 'bereiche', 'sonstiges'])->change();

            // Make machine nullable (was required before)
            $table->string('machine')->nullable()->change();

            // Add new columns
            $table->string('department')->nullable()->after('machine');
            $table->string('error_code')->nullable()->after('department');

            $table->longText('problem')->after('error_code');
            $table->longText('solution')->nullable()->after('problem');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedback', function (Blueprint $table) {

            // Revert type
            $table->enum('type', ['fehler', 'vorschlag', 'anleitung'])->change();

            // Revert machine
            $table->string('machine')->nullable(false)->change();

            // Drop new columns
            $table->dropColumn([
                'department',
                'error_code',
                'problem',
                'solution',
            ]);

            // Restore old columns
            $table->longText('description');
            $table->enum('priority', ['niedrig', 'mittel', 'hoch'])->default('niedrig');
            $table->enum('ai_solution', ['ja', 'nein', 'naja']);
        });
    }
};
