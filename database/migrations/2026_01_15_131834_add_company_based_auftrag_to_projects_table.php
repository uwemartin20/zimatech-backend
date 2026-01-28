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
        Schema::table('projects', function (Blueprint $table) {
            // Rename existing column
            $table->renameColumn('auftragsnummer', 'auftragsnummer_zt');

            // Add new column
            $table->string('auftragsnummer_zf')
                  ->nullable()
                  ->after('auftragsnummer_zt')
                  ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['auftragsnummer_zf']);
            $table->dropColumn('auftragsnummer_zf');

            $table->renameColumn('auftragsnummer_zt', 'auftragsnummer');
        });
    }
};
