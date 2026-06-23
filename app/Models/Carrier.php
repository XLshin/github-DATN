<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrier extends Model
{
    protected $fillable = [
        'name',
        'code',
        'api_credentials',
        'webhook_secret',
        'active'
    ];

    protected $casts = [
        'api_credentials' => 'array',
        'active' => 'boolean',
    ];

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}
