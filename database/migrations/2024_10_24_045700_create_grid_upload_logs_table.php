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
        Schema::create('grid_upload_logs', function (Blueprint $table) {
            $table->id();
            $table->string('upload_id');
            $table->string('agent_id')->nullable();
            $table->string('comany_name')->nullable();
            $table->string('created_month');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grid_upload_logs');
    }
};
