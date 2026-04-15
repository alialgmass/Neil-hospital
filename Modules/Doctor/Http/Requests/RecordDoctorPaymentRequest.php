<?php

namespace Modules\Doctor\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordDoctorPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => 'required|string|exists:doctors,id',
            'amount' => 'required|numeric|min:0.01',
            'period_from' => 'required|date',
            'period_to' => 'required|date|after_or_equal:period_from',
            'notes' => 'nullable|string',
        ];
    }
}
