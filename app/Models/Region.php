<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Region extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // public function states(): HasMany
    // {
    //     return $this->hasMany(State::class);
    // }

    public function circles(): HasManyThrough
    {
        return $this->hasManyThrough(Circle::class, State::class);
    }

    public function sections(): HasManyThrough
    {
        return $this->hasManyThrough(Section::class, State::class);
    }

    public function commissionRates(): HasManyThrough
    {
        return $this->hasManyThrough(CommissionRate::class, State::class);
    }

    public function states()
    {
        return $this->hasMany(State::class);
    }
}
