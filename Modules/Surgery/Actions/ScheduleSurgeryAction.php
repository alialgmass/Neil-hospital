<?php

namespace Modules\Surgery\Actions;

use App\Services\ActivityLogService;
use Illuminate\Validation\ValidationException;
use Modules\Surgery\DTOs\SurgeryData;
use Modules\Surgery\Models\Surgery;
use Modules\Surgery\Services\SurgeryService;

class ScheduleSurgeryAction
{
    public function __construct(
        private readonly SurgeryService $surgeryService,
        private readonly ActivityLogService $activityLog,
    ) {}

    public function execute(SurgeryData $data): Surgery
    {
        if ($data->orBedId && $data->scheduledAt) {
            if (! $this->surgeryService->isBedAvailable($data->orBedId, $data->scheduledAt)) {
                throw ValidationException::withMessages([
                    'or_bed_id' => 'السرير المحدد محجوز في هذا الوقت.',
                ]);
            }
        }

        $existing = $this->surgeryService->findByBooking($data->bookingId);

        if ($existing) {
            $oldBedId = $existing->or_bed_id;
            $surgery = $this->surgeryService->update($existing->id, $data);

            if ($oldBedId && $oldBedId !== $data->orBedId) {
                $this->surgeryService->markBedAvailable($oldBedId);
            }
        } else {
            $surgery = $this->surgeryService->schedule($data);
        }

        if ($data->orBedId) {
            $this->surgeryService->markBedOccupied($data->orBedId);
        }

        $this->activityLog->log(
            action: 'scheduled',
            module: $data->dept,
            recordId: $surgery->id,
            description: "جدولة {$data->dept} للحجز: {$data->bookingId}",
        );

        return $surgery;
    }
}
