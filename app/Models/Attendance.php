<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'status',
        'check_in_time',
        'check_out_time',
    ];
    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];
        
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeWorkingDays($query, $userId)
    {
        return $query->where('user_id', $userId)
                     ->whereMonth('check_in_time', now()->month)
                     ->whereYear('check_in_time', now()->year)
                     ->count();
    }

    public static function getWorkingDays($userId)
    {
        return self::workingDays($userId);
    }
}
