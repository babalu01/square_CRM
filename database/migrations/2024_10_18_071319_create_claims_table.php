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
        Schema::create('claims', function (Blueprint $table) {
            $table->id(); // Claim ID (Auto-generated)
            $table->string('policy_number')->nullable(); // Policy Number (Text)
            $table->enum('claim_status', ['Filed', 'In Review', 'Settled', 'Denied'])->nullable(); // Claim Status (Dropdown)
            $table->date('date_filed')->nullable(); // Date Filed (Date Picker)
            $table->decimal('settlement_amount', 10, 2)->nullable(); // Settlement Amount (Currency)
            $table->string('documentation')->nullable(); // Documentation (File Upload)
            $table->text('communication_log')->nullable(); // Communication Log (Text Area)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
