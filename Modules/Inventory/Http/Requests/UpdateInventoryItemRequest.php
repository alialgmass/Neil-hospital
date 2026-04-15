<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:200',
            'code' => 'nullable|string|max:40|unique:inventory,code,'.$this->route('id'),
            'category' => 'nullable|string|max:80',
            'unit' => 'nullable|string|max:30',
            'quantity' => 'nullable|numeric|min:0',
            'min_quantity' => 'nullable|numeric|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'sell_price' => 'nullable|numeric|min:0',
            'supplier_id' => 'nullable|string|exists:suppliers,id',
            'expiry_date' => 'nullable|date',
            'location' => 'nullable|string|max:80',
            'notes' => 'nullable|string',
        ];
    }
}
