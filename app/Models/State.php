<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'region_id'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function commissionRates()
    {
        return $this->hasMany(CommissionRate::class);
    }

    public function circles()
    {
        return $this->hasManyThrough(Circle::class, CommissionRate::class, 'state_id', 'id', 'id', 'circle_id');
    }
    
}
