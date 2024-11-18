<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GridGroup extends Model
{
    use HasFactory;
    protected $table = 'agent_grid';

    protected $fillable = ['group_name', 'value']; // Add fillable fields
}
