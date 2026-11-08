<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequisitionItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'requisition_id', 'requisition_version_id', 'item_id',
        'item_code_snapshot', 'item_name_snapshot', 'category_name_snapshot',
        'unit_name_snapshot', 'quantity_requested', 'quantity_manager_approved',
        'quantity_ga_recommended', 'quantity_ga_final', 'quantity_sam_approved',
        'quantity_sm_approved', 'reference_unit_price', 'estimated_subtotal',
        'need_reason', 'ga_change_reason', 'quantity_change_log',
    ];

    protected function casts(): array
    {
        return [
            'quantity_requested' => 'decimal:2',
            'quantity_manager_approved' => 'decimal:2',
            'quantity_ga_recommended' => 'decimal:2',
            'quantity_ga_final' => 'decimal:2',
            'quantity_sam_approved' => 'decimal:2',
            'quantity_sm_approved' => 'decimal:2',
            'reference_unit_price' => 'decimal:2',
            'estimated_subtotal' => 'decimal:2',
            'quantity_change_log' => 'array',
        ];
    }

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(RequisitionVersion::class, 'requisition_version_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
