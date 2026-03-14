<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoveItem extends Model
{
    protected $fillable = [
        'move_id',
        'product_id',
        'quantity',
    ];

    public function move()
    {
        return $this->belongsTo(Move::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
