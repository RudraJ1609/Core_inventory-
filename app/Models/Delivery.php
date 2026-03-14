<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $fillable = [
        'number',
        'customer',
        'warehouse_id',
        'status',
        'scheduled_at',
        'validated_at',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'validated_at' => 'datetime',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(DeliveryItem::class);
    }
}
