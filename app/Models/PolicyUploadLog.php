<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyUploadLog extends Model
{
    use HasFactory;
    protected $table = 'policy_upload_log';

    protected $fillable = [
        'upload_id',
        'user_id',
        'date',
    ];
}
