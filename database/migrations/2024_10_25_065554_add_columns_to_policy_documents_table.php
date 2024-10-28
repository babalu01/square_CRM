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
        Schema::table('policy_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_id')->nullable(); // Add agent_id as nullable
            $table->unsignedBigInteger('policy_id')->nullable()->change(); // Change policy_id to nullable
            $table->string('vehicle_no')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_documents', function (Blueprint $table) {
            $table->dropColumn('agent_id'); // Drop agent_id
            $table->unsignedBigInteger('policy_id')->nullable(false)->change(); // Revert policy_id to not nullable
            $table->string('vehicle_no')->nullable(false)->change();
        });
    }
};
