<?php

namespace Modules\Doctor\Actions;

use Modules\Admin\Services\ActivityLogService;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Models\DoctorPayment;

class RecordDoctorPaymentAction
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function execute(array $data): DoctorPayment
    {
        $payment = DoctorPayment::create([
            ...$data,
            'paid_by' => auth()->id(),
        ]);

        $doctor = Doctor::findOrFail($data['doctor_id']);

        $this->activityLogService->log(
            action: 'payment',
            module: 'doctor',
            recordId: $payment->id,
            description: "صرف مستحقات للدكتور {$doctor->name}: {$payment->amount} ج",
        );

        return $payment;
    }
}
