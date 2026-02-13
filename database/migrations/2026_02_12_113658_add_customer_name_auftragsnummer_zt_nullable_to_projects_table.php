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
            // make auftragsnummer_zt nullable
            $table->string('auftragsnummer_zt')
                ->nullable()
                ->change();

            // add customer_name column
            $table->string('customer_name')
                ->nullable()
                ->after('auftragsnummer_zt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // revert nullable change
            $table->string('auftragsnummer_zt')
                ->nullable(false)
                ->change();

            // drop new column
            $table->dropColumn('customer_name');
        });
    }
};
