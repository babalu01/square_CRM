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
        Schema::table('grid_upload_logs', function (Blueprint $table) {
            $table->boolean('active_status')->default(0); // Add active_status column with default value 0
            $table->string('approved_by')->nullable(); // Add approved_by column as nullable
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grid_upload_logs', function (Blueprint $table) {
            $table->dropColumn('active_status'); // Drop active_status column
            $table->dropColumn('approved_by'); // Drop approved_by column
        });
    }
};
