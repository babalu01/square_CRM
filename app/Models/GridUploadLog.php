<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GridUploadLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'upload_id',
        'agent_id',
        'comany_name',
        'created_month',
    ];

    /**
     * Get the commission rates for the grid upload log.
     */
    public function commissionRates(): HasMany
    {
        return $this->hasMany(CommissionRate::class, 'upload_id', 'upload_id');
    }
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'agent_id', 'id');
    }

    public function policiesCompany(): BelongsTo
    {
        return $this->belongsTo(PoliciesCompany::class, 'comany_name', 'id'); // Corrected connection to PoliciesCompany table
    }
}
