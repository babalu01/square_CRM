<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'company_name']; // Added 'company_name' to fillable fields

    public function insuranceRates()
    {
        return $this->hasMany(CommissionRate::class);
    }
}
