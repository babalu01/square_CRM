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
            $table->unsignedBigInteger('policy_details_id')->nullable();
            $table->foreign('policy_details_id')->references('id')->on('policy_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_documents', function (Blueprint $table) {
            $table->dropForeign(['policy_details_id']);
            $table->dropColumn('policy_details_id');
        });
    }
};
