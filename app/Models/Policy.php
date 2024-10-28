<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Policy extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_number',
        'type',
        'provider',
        'premium_amount',
        'start_date',
        'end_date',
        'status',
        'company',
        'product',
        'mfg_year',
        'fuel_type',
        'gvw_cc',
        'policy_holder_name',
        'od',
        'without_gst',
        'total',
        'registration_number',
        'policy_type',
        'agent_name',
        'broker_direct_code',
        'mode_of_payment',
        'percentage',
        'commission',
        'tds',
        'final_commission',
        'discount_percentage',
        'discount',
        'payment',
        'cheque_no',
        'payment_received',
        'profit',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'agent_name', 'id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PolicyDocument::class);
    }
}
