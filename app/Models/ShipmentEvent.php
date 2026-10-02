<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentEvent extends Model
{
    protected $fillable = [
        'shipment_id',
        'event_type',
        'location',
        'description',
        'event_time'
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}