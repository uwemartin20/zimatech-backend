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
        Schema::table('offer_calculations', function (Blueprint $table) {
            $table->decimal('extra_tax', 5, 2)->nullable()->after('external_cost');
            $table->decimal('final_offer', 5, 2)->nullable()->after('extra_tax');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_calculations', function (Blueprint $table) {
            //
        });
    }
};
