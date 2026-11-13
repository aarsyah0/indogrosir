<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProcurementOrder extends Model
{
    protected $fillable = [
        'number', 'requisition_id', 'supplier_id', 'purchased_at', 'invoice_number',
        'discount', 'tax', 'extra_cost', 'actual_total', 'over_qty_authorized',
        'over_qty_reason', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'purchased_at' => 'date',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'extra_cost' => 'decimal:2',
            'actual_total' => 'decimal:2',
            'over_qty_authorized' => 'boolean',
        ];
    }

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProcurementOrderItem::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
