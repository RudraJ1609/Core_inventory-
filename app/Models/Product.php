<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'category',
        'unit_of_measure',
        'reorder_point',
        'initial_stock',
        'is_active',
    ];

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function receiptItems()
    {
        return $this->hasMany(ReceiptItem::class);
    }

    public function deliveryItems()
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function moveItems()
    {
        return $this->hasMany(MoveItem::class);
    }

    public function adjustments()
    {
        return $this->hasMany(Adjustment::class);
    }

    public function stockLedgers()
    {
        return $this->hasMany(StockLedger::class);
    }
}
