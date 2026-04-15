<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockPermitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department' => 'nullable|string|max:80',
            'reason' => 'nullable|string|max:300',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|string|exists:inventory,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.item_name' => 'nullable|string|max:200',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
        ];
    }
}
