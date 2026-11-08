<?php

namespace App\Models;

use App\Enums\ProcurementStatus;
use App\Enums\ReceiptStatus;
use App\Enums\RequisitionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Requisition extends Model
{
    protected $fillable = [
        'number', 'request_period_id', 'division_id', 'requester_id', 'status',
        'procurement_status', 'receipt_status', 'current_version', 'submitted_at',
        'notes', 'estimated_total',
    ];

    protected function casts(): array
    {
        return [
            'status' => RequisitionStatus::class,
            'procurement_status' => ProcurementStatus::class,
            'receipt_status' => ReceiptStatus::class,
            'submitted_at' => 'datetime',
            'estimated_total' => 'decimal:2',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(RequestPeriod::class, 'request_period_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(RequisitionVersion::class);
    }

    public function latestVersionRecord(): HasOne
    {
        return $this->hasOne(RequisitionVersion::class)->ofMany('version_number', 'max');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RequisitionItem::class);
    }

    public function currentItems()
    {
        return $this->items()->whereHas('version', function ($q) {
            $q->whereColumn('requisition_versions.version_number', 'requisitions.current_version');
        });
    }

    public function approvalHistories(): HasMany
    {
        return $this->hasMany(ApprovalHistory::class);
    }

    public function approvalSteps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class);
    }

    public function procurementOrders(): HasMany
    {
        return $this->hasMany(ProcurementOrder::class);
    }

    public function latestVersion(): ?RequisitionVersion
    {
        return $this->versions()->where('version_number', $this->current_version)->first();
    }

    public function latestItems()
    {
        $version = $this->latestVersion();

        if (! $version) {
            return $this->items()->whereRaw('1 = 0');
        }

        return $this->items()->where('requisition_version_id', $version->id);
    }
}
