<?php

namespace Modules\Booking\Actions;

use App\Services\ActivityLogService;
use Modules\Booking\DTOs\BookingData;
use Modules\Booking\Models\Booking;
use Modules\Booking\Services\BookingService;
use Modules\Insurance\Models\InsuranceClaim;
use Modules\Surgery\DTOs\SurgeryData;
use Modules\Surgery\Services\SurgeryService;

class CreateBookingAction
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly SurgeryService $surgeryService,
        private readonly ActivityLogService $activityLog,
    ) {}

    public function execute(BookingData $data, int $createdBy): Booking
    {
        $booking = $this->bookingService->create($data, $createdBy);

        if ($data->payMethod === 'insurance' && $data->insCompanyId) {
            $patientShare = max(0, $data->price - $data->discount - $data->insAmount);

            InsuranceClaim::create([
                'booking_id' => $booking->id,
                'insurance_company_id' => $data->insCompanyId,
                'service_id' => $data->serviceId,
                'patient_name' => $data->patientName,
                'file_no' => $booking->file_no,
                'service_name' => $data->serviceName ?? '',
                'invoice_amount' => $data->price,
                'discount' => $data->discount,
                'insurance_share' => $data->insAmount,
                'patient_share' => $patientShare,
                'approved_amount' => 0,
                'paid_amount' => 0,
                'status' => 'draft',
                'service_date' => $data->visitDate,
                'claim_date' => today()->toDateString(),
                'created_by' => $createdBy,
            ]);
        }

        if (in_array($data->dept, ['surgery', 'lasik'])) {
            $aa = $this->surgeryService->schedule(new SurgeryData(
                bookingId: $booking->id,
                dept: $data->dept,
                orBedId: $data->bedId,
                surgeonId: $data->doctorId,
            ));

        }

        $this->activityLog->log(
            action: 'created',
            module: 'booking',
            recordId: $booking->id,
            description: "حجز جديد: {$booking->patient_name} — {$booking->file_no}",
            newValues: $booking->toArray(),
        );

        return $booking;
    }
}
