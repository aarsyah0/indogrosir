<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementOrderItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'procurement_order_id', 'item_id', 'item_code_snapshot', 'item_name_snapshot',
        'unit_name_snapshot', 'quantity_approved', 'quantity_purchased', 'unit_price', 'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'quantity_approved' => 'decimal:2',
            'quantity_purchased' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ProcurementOrder::class, 'procurement_order_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
