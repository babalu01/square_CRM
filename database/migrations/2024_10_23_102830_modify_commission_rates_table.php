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
        Schema::table('commission_rates', function (Blueprint $table) {
            $table->dropColumn('comp_net'); // Drop the old comp_net column
            $table->dropColumn('satp_net'); // Drop the old satp_net column
            $table->string('section_id'); // Add new section_id column
            $table->float('value'); // Add new value column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commission_rates', function (Blueprint $table) {
            $table->float('comp_net'); // Re-add the old comp_net column
            $table->float('satp_net'); // Re-add the old satp_net column
            $table->dropColumn('section_id'); // Drop the new section_id column
            $table->dropColumn('value'); // Drop the new value column
        });
    }
};
