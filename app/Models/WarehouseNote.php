<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseNote extends Model
{
    protected $fillable = [
        'shipment_id',
        'author_id',
        'note',
        'visibility'
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}