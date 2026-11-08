<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GaVerifyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('approvals.ga') ?? false;
    }

    public function rules(): array
    {
        return [
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.id' => ['required', 'exists:requisition_items,id'],
            'lines.*.quantity_ga_final' => ['required', 'numeric', 'min:0'],
            'lines.*.quantity_ga_recommended' => ['nullable', 'numeric', 'min:0'],
            'lines.*.ga_change_reason' => ['nullable', 'string', 'max:1000'],
            'return_comment' => ['nullable', 'string', 'max:2000'],
            'intent' => ['required', 'in:save,submit_sam,return_division'],
        ];
    }
}
