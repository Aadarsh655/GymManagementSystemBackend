<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    use HasFactory;

    protected $primaryKey = 'membership_id';

    protected $fillable = [
        'membership_name',
        'price',
        'facilities',
        'status',
    ];

    protected $casts = [
        'facilities' => 'array', // Automatically cast JSON to array
    ];
}
