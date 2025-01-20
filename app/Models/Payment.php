<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $primaryKey = 'payment_id';

    protected $fillable=[
        'user_id',
        'membership_id',
        'amount',
        'discount',
        'paid_amount',
        'paid_date',
        'expire_date',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function membership()
    {
        return $this->belongsTo(Membership::class, 'membership_id', 'membership_id');
    }
}
