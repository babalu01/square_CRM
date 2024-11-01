<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;
    protected $table = 'sections';

    // Specify the fillable attributes for mass assignment
    protected $fillable = [
        'name',
    ];
}
