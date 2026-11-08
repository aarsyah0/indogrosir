<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequisitionVersion extends Model
{
    protected $fillable = [
        'requisition_id', 'version_number', 'created_by', 'reason',
        'estimated_total', 'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'locked_at' => 'datetime',
            'estimated_total' => 'decimal:2',
        ];
    }

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RequisitionItem::class);
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }
}
