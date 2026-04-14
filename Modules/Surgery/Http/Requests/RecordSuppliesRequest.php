<?php

namespace Modules\Surgery\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordSuppliesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('surgery.write') ?? false;
    }

    public function rules(): array
    {
        return [
            'surgery_id'          => ['required', 'exists:surgeries,id'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.item_id'     => ['required', 'string'],
            'items.*.name'        => ['required', 'string'],
            'items.*.qty'         => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_cost'   => ['required', 'numeric', 'min:0'],
            'items.*.total'       => ['required', 'numeric', 'min:0'],
        ];
    }
}
