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
            $table->string('created_month')->nullable();
            $table->unsignedBigInteger('upload_id')->nullable();
            $table->unsignedBigInteger('circle_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commission_rates', function (Blueprint $table) {
            $table->dropColumn('created_month');
            $table->dropColumn('upload_id');
            $table->dropColumn('circle_id');
        });
    }
};
