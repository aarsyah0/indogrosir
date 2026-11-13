<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('receipts.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'procurement_id' => ['required', 'exists:procurement_orders,id'],
            'submission_key' => ['required', 'uuid'],
            'received_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id', 'distinct'],
            'items.*.quantity_received' => ['required', 'numeric', 'min:0'],
            'items.*.quantity_short' => ['nullable', 'numeric', 'min:0'],
            'items.*.condition' => ['required', 'in:baik,rusak,tidak_sesuai'],
            'items.*.discrepancy_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
