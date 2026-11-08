<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'code', 'name', 'item_category_id', 'unit_id', 'description',
        'reference_price', 'min_stock', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'reference_price' => 'decimal:2',
            'min_stock' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(ItemPriceHistory::class);
    }

    public function stockTransactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function currentStock(): float
    {
        $last = $this->stockTransactions()->latest('id')->first();

        return $last ? (float) $last->balance_after : 0.0;
    }

    public function hasHistoricalTransactions(): bool
    {
        return $this->stockTransactions()->exists()
            || RequisitionItem::query()->where('item_id', $this->id)->exists()
            || ProcurementOrderItem::query()->where('item_id', $this->id)->exists();
    }
}
