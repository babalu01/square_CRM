<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'state_id',
        'vehicle_category_id',
        'section_id',
        'value',
        'is_new',
        'created_month',
        'upload_id',
        'circle_id',
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function vehicleCategory(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
    
  

    public function circle(): BelongsTo
    {
        return $this->belongsTo(Circle::class);
    }
}
