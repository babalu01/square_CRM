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
            $table->unsignedBigInteger('upload_id')->nullable(); // Add upload_id column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_documents', function (Blueprint $table) {
            $table->dropColumn('upload_id'); // Drop upload_id column
        });
    }
};
