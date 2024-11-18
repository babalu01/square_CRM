<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoliciesCompany extends Model
{
    use HasFactory;
    protected $table = 'policies_company'; // Specify the table associated with the model

    protected $fillable = ['company_name']; // Add fillable properties here
}
