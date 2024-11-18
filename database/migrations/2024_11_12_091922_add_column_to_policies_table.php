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
        Schema::table('policies', function (Blueprint $table) {
            $table->string('upload_id')->nullable(); // Add upload_id column
            $table->boolean('verification_status')->default(0); // Add verification_status column with default value 0
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->dropColumn('upload_id'); // Drop upload_id column
            $table->dropColumn('verification_status'); // Drop verification_status column
        });
    }
};
