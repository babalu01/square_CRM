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
        Schema::create('policies', function (Blueprint $table) {
            $table->id(); // Policy ID (Auto-increment)
            $table->string('policy_number'); // Policy Number (Text)
            $table->string('type'); // Type (Text)
            $table->string('provider'); // Provider (Text)
            $table->decimal('premium_amount', 10, 2); // Premium Amount (Currency)
            $table->date('start_date'); // Start Date (Date)
            $table->date('end_date'); // End Date (Date)
            $table->string('status'); // Status (Text)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
