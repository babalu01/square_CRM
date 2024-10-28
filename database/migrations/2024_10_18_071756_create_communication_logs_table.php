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
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id(); // Communication ID (Auto-increment)
            $table->foreignId('client_id')->constrained(); // Client ID (Foreign Key)
            $table->date('date'); // Date (Date)
            $table->string('type'); // Type (Text)
            $table->text('notes'); // Notes (Text Area)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
    }
};
