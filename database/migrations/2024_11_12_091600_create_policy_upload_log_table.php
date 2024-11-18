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
        Schema::create('policy_upload_log', function (Blueprint $table) {
            $table->id();
            $table->string('upload_id')->nullable(); // Add upload_id column
            $table->string('user_id')->nullable(); // Add user_id column
            $table->date('date')->nullable(); // Add date column
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_upload_log');
    }
};
