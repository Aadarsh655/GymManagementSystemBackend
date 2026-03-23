<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteMediaAsset extends Model
{
    protected $fillable = [
        'section',
        'item_key',
        'label',
        'usage_hint',
        'media_type',
        'recommended_size',
        'sort_order',
        'file_path',
    ];
}

