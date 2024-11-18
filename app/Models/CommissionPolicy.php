<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionPolicy extends Model
{
    use HasFactory;
    protected $table = 'commission_policy';

    protected $fillable = [
        'state',
        'int_cluster',
        'vehicle_type',
        'fuel_type',
        'age_group',
        'engine_capacity',
        'condition_type',
        'premium_type',
        'basis',
        'amount',
        'company_id',
        'product',
        'upload_id',
    ];

    // Define relationships with other tables
    public function company(): BelongsTo
    {
        return $this->belongsTo(PoliciesCompany::class, 'company_id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state');
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_type');
    }

    // Add other relationships as needed
}
