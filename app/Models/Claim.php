<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_number',
        'claim_status',
        'date_filed',
        'settlement_amount',
        'documentation',
        'communication_log',
    ];
}
