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
        Schema::create('commission_policy', function (Blueprint $table) {
            $table->id();
            $table->string('state', 50)->nullable();
            $table->string('int_cluster', 50)->nullable();
            $table->string('vehicle_type', 100)->nullable();
            $table->string('fuel_type', 20)->nullable();
            $table->string('age_group', 50)->nullable();
            $table->string('engine_capacity', 50)->nullable();
            $table->string('condition_type', 50)->nullable(); 
            $table->string('premium_type', 50)->nullable(); // e.g., Comp, STP, Comp & STP
            $table->string('basis', 50)->nullable(); // e.g., OD (Own Damage), GWP (Gross Written Premium)
            $table->decimal('amount', 10, 2)->nullable(); // this field
            $table->string('company_id')->nullable(); // this field
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_policy');
    }
};
