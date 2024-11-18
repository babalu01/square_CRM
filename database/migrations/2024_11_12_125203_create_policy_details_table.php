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
        Schema::create('policy_details', function (Blueprint $table) {
            $table->id();
            $table->string('CustomerName')->nullable();
            $table->string('Partner_Name')->nullable();
            $table->string('Partner_Code')->nullable();
            $table->string('Insurer_Name')->nullable();
            $table->string('Business_Type')->nullable();
            $table->string('LOB')->nullable();
            $table->string('Product')->nullable();
            $table->string('Sub_Product')->nullable();
            $table->string('Segment')->nullable();
            $table->string('Plan_Type')->nullable();
            $table->string('Class_Name')->nullable();
            $table->string('Sub_Class')->nullable();
            $table->string('Vehicle_No')->nullable();
            $table->string('Policy_No')->nullable();
            $table->date('Policy_Issue_Date')->nullable();
            $table->date('PolicyStartDateTP')->nullable();
            $table->date('PolicyEndDateTP')->nullable();
            $table->decimal('NCB', 10, 2)->nullable();
            $table->decimal('IDV', 10, 2)->nullable();
            $table->string('Payment_Mode')->nullable();
            $table->string('Payment_Towards')->nullable();
            $table->string('Payment_Cheque_Ref_No')->nullable();
            $table->decimal('GrossPrem', 10, 2)->nullable();
            $table->decimal('NetPrem', 10, 2)->nullable();
            $table->decimal('OD_Premium', 10, 2)->nullable();
            $table->decimal('TP_Premium', 10, 2)->nullable();
            $table->decimal('LPA_Partner_Payout_OD%', 5, 2)->nullable();
            $table->decimal('LPA_Partner_Payout_OD_Amount', 10, 2)->nullable();
            $table->decimal('LPA_Partner_Payout_Net%', 5, 2)->nullable();
            $table->decimal('LPA_Partner_Payout_Net_Amount', 10, 2)->nullable();
            $table->decimal('LPA_Partner_Total_Amount', 10, 2)->nullable();
            $table->text('REMARK')->nullable();
            $table->text('STATUS')->nullable();
            $table->boolean('is_verified')->default(0);
            $table->string('upload_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_details');
    }
};
