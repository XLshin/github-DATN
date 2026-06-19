<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warranty extends Model
{
    protected $fillable = [
        'imei_id',
        'order_id',
        'warranty_start',
        'warranty_end',
        'status'
    ];

    protected $casts = [
        'warranty_start' => 'date',
        'warranty_end' => 'date',
    ];

    public function imei()
    {
        return $this->belongsTo(Imei::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
