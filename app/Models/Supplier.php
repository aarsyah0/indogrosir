<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'code', 'name', 'contact_name', 'phone', 'email', 'address', 'notes', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'supplier_items')->withPivot('last_price');
    }

    public function procurementOrders(): HasMany
    {
        return $this->hasMany(ProcurementOrder::class);
    }

    public function hasHistoricalTransactions(): bool
    {
        return $this->procurementOrders()->exists();
    }
}
