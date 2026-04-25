<?php

namespace Modules\Surgery\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordSuppliesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'surgery_id' => ['required_without:supplies', 'exists:surgeries,id'],
            'supplies' => ['required_without:surgery_id', 'array', 'min:1'],
            'supplies.*.inventory_item_id' => ['required', 'string'],
            'supplies.*.name' => ['required', 'string'],
            'supplies.*.qty' => ['required', 'numeric', 'min:1'],
            'supplies.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        if (isset($data['supplies']) && ! isset($data['surgery_id'])) {
            $data['surgery_id'] = $this->route('id');
            $data['items'] = $data['supplies'];
        }

        return $data;
    }
}
