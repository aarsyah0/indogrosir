<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequisitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('requisitions.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'request_period_id' => ['required', 'exists:request_periods,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'distinct', 'exists:items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.need_reason' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Minimal satu barang harus diisi.',
            'items.*.item_id.distinct' => 'Barang duplikat tidak diizinkan.',
            'items.*.need_reason.required' => 'Alasan kebutuhan wajib diisi.',
        ];
    }
}
