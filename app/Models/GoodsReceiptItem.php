<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'goods_receipt_id', 'item_id', 'quantity_purchased', 'quantity_received',
        'quantity_short', 'condition', 'discrepancy_notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_purchased' => 'decimal:2',
            'quantity_received' => 'decimal:2',
            'quantity_short' => 'decimal:2',
        ];
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class, 'goods_receipt_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
