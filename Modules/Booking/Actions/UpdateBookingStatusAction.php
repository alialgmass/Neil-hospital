<?php

namespace Modules\Booking\Actions;

use App\Services\ActivityLogService;
use Illuminate\Validation\ValidationException;
use Modules\Booking\Models\Booking;
use Modules\Booking\Repositories\Contracts\BookingRepositoryInterface;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;

class UpdateBookingStatusAction
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly ActivityLogService $activityLog,
    ) {}

    public function execute(string $id, string $newStatus, ?string $cancelReason = null): Booking
    {
        $booking = $this->bookingRepository->findOrFail($id);
        $oldStatus = (string) $booking->status;

        try {
            $booking->status->transitionTo($newStatus);
        } catch (CouldNotPerformTransition $e) {
            throw ValidationException::withMessages([
                'status' => "لا يمكن الانتقال من حالة \"{$oldStatus}\" إلى \"{$newStatus}\".",
            ]);
        }

        if ($newStatus === 'cancelled') {
            $booking->update(['cancel_reason' => $cancelReason]);
        }

        $this->activityLog->log(
            action: 'status_changed',
            module: 'booking',
            recordId: $booking->id,
            description: "تغيير حالة الحجز {$booking->file_no}: {$oldStatus} → {$newStatus}",
        );

        return $booking->fresh();
    }
}
