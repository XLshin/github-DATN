<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderReceiver extends Model
{
    protected $fillable = [
        'order_id',
        'receiver_name',
        'receiver_phone',
        'receiver_address',
        'receiver_note',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}