<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequestPeriod extends Model
{
    protected $fillable = [
        'name', 'year', 'month', 'start_date', 'deadline_date', 'status', 'opened_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'deadline_date' => 'date',
        ];
    }

    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function requisitions(): HasMany
    {
        return $this->hasMany(Requisition::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open'
            && now()->toDateString() >= $this->start_date->toDateString()
            && now()->toDateString() <= $this->deadline_date->toDateString();
    }
}
