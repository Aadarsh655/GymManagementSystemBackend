<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
        'mobile',
        'email',
        'logo',
        'facebook',
        'instagram',
        'twitter',
        'linkedin',
        'tiktok',
        'other',
    ];

    protected $casts = [
        'other' => 'array',
    ];
}
