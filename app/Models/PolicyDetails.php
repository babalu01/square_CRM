<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class PolicyDetails extends Model
{
    use HasFactory;
    protected $table = 'policy_details';

    protected $fillable = [
        'CustomerName',
        'Partner_Name',
        'Partner_Code',
        'Insurer_Name',
        'Business_Type',
        'LOB',
        'Product',
        'Sub_Product',
        'Segment',
        'Plan_Type',
        'Class_Name',
        'Sub_Class',
        'Vehicle_No',
        'Policy_No',
        'Policy_Issue_Date',
        'PolicyStartDateTP',
        'PolicyEndDateTP',
        'NCB',
        'IDV',
        'Payment_Mode',
        'Payment_Towards',
        'Payment_Cheque_Ref_No',
        'GrossPrem',
        'NetPrem',
        'OD_Premium',
        'TP_Premium',
        'LPA_Partner_Payout_OD%',
        'LPA_Partner_Payout_OD_Amount',
        'LPA_Partner_Payout_Net%',
        'LPA_Partner_Payout_Net_Amount',
        'LPA_Partner_Total_Amount',
        'REMARK',
        'STATUS',
        'is_verified',
        'upload_id',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'agent_name', 'id');
    }

    public function policydocuments(): HasMany
    {
        return $this->hasMany(PolicyDocument::class, 'policy_details_id', 'id');
    }
}
