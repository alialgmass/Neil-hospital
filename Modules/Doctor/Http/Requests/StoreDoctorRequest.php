<?php

namespace Modules\Doctor\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'specialty' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'fee_type' => 'required|in:percentage,fixed,insurance',
            'fee_value' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }
}
