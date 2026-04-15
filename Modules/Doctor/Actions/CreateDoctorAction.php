<?php

namespace Modules\Doctor\Actions;

use Modules\Admin\Services\ActivityLogService;
use Modules\Doctor\Models\Doctor;

class CreateDoctorAction
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function execute(array $data): Doctor
    {
        $doctor = Doctor::create($data);

        $this->activityLogService->log(
            action: 'create',
            module: 'doctor',
            recordId: $doctor->id,
            description: "إضافة طبيب: {$doctor->name}",
        );

        return $doctor;
    }
}
