<?php

namespace Modules\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string', 'max:100'],
            'settings.*.value' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'settings.required' => 'بيانات الإعدادات مطلوبة.',
        ];
    }
}
