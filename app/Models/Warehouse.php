<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = [
        'name',
        'code',
        'location',
        'is_active',
    ];

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function adjustments()
    {
        return $this->hasMany(Adjustment::class);
    }

    public function stockLedgers()
    {
        return $this->hasMany(StockLedger::class);
    }

    public function movesFrom()
    {
        return $this->hasMany(Move::class, 'from_warehouse_id');
    }

    public function movesTo()
    {
        return $this->hasMany(Move::class, 'to_warehouse_id');
    }
}
