<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'tracking_number',
        'customer_name',
        'origin',
        'destination',
        'shipped_at',
        'is_restricted'
    ];

    public function events()
    {
        return $this->hasMany(ShipmentEvent::class, 'shipment_id');
    }

    public function notes()
    {
        return $this->hasMany(WarehouseNote::class, 'shipment_id');
    }
}