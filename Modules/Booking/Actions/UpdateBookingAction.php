<?php

namespace Modules\Booking\Actions;

use App\Services\ActivityLogService;
use Illuminate\Validation\ValidationException;
use Modules\Accounting\Actions\AutoPostBookingPaymentAction;
use Modules\Booking\DTOs\BookingData;
use Modules\Booking\Models\Booking;
use Modules\Booking\Services\BookingService;

class UpdateBookingAction
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly ActivityLogService $activityLog,
        private readonly AutoPostBookingPaymentAction $autoPost,
    ) {}

    public function execute(string $id, BookingData $data): Booking
    {
        $old = $this->bookingService->findOrFail($id);

        // Paid bookings cannot be edited (financial integrity)
        if ($old->pay_status === 'paid') {
            throw ValidationException::withMessages([
                'pay_status' => 'لا يمكن تعديل حجز مسدد بالكامل.',
            ]);
        }

        $booking = $this->bookingService->update($id, $data);

        // Auto-post accounting entries when payment is first confirmed
        if ($old->pay_status !== 'paid' && $booking->pay_status === 'paid') {
            $this->autoPost->execute($booking);
        }

        $this->activityLog->log(
            action:      'updated',
            module:      'booking',
            recordId:    $booking->id,
            description: "تعديل حجز: {$booking->patient_name} — {$booking->file_no}",
            oldValues:   $old->toArray(),
            newValues:   $booking->toArray(),
        );

        return $booking;
    }
}
