<?php

namespace Modules\Doctor\Actions;

use Modules\Admin\Services\ActivityLogService;
use Modules\Doctor\Models\DoctorShift;
use Modules\Doctor\Services\DoctorService;

class OpenDoctorShiftAction
{
    public function __construct(
        private readonly DoctorService $doctorService,
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function execute(string $doctorId, string $shiftDate, ?string $notes = null): DoctorShift
    {
        $shift = $this->doctorService->openShift($doctorId, $shiftDate, $notes);

        $this->activityLogService->log(
            action: 'shift_open',
            module: 'doctor',
            recordId: $shift->id,
            description: "فتح وردية للطبيب {$doctorId} بتاريخ {$shiftDate}",
        );

        return $shift;
    }
}
