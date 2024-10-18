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
           
            $table->string('company')->nullable();
            $table->string('product')->nullable();
            $table->integer('mfg_year')->nullable();
            $table->string('fuel_type')->nullable();
            $table->string('gvw_cc')->nullable();
            $table->string('policy_holder_name')->nullable();
            $table->decimal('od', 10, 2)->nullable();
            $table->decimal('without_gst', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->string('registration_number')->nullable();
            $table->string('policy_type')->nullable();
            $table->string('agent_name')->nullable();
            $table->string('broker_direct_code')->nullable();
            $table->string('mode_of_payment')->nullable();
          
            $table->decimal('percentage', 5, 2)->nullable();
            $table->decimal('commission', 10, 2)->nullable();
            $table->decimal('tds', 10, 2)->nullable();
            $table->decimal('final_commission', 10, 2)->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->decimal('payment', 10, 2)->nullable();
            $table->string('cheque_no')->nullable();
            $table->decimal('payment_received', 10, 2)->nullable();
            $table->decimal('profit', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->dropColumn([
                  'company', 'product', 'mfg_year', 'fuel_type',
                'gvw_cc', 'policy_holder_name', 'od', 'without_gst', 'total', 'from_date', 'to_date',
                'registration_number', 'policy_type', 'agent_name', 'broker_direct_code',
                'mode_of_payment', 'id', 'percentage', 'commission', 'tds', 'final_commission',
                'discount_percentage', 'discount', 'payment', 'cheque_no', 'payment_received', 'profit'
            ]);
        });
    }
};
