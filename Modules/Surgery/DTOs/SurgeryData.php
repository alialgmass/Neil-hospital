<?php

namespace Modules\Surgery\DTOs;

readonly class SurgeryData
{
    public function __construct(
        public string $bookingId,
        public string $dept,
        public ?int $orBedId = null,
        public ?int $bedNo = null,
        public ?string $surgeonId = null,
        public ?string $eye = null,
        public ?string $procedure = null,
        public ?string $anaesthesia = null,
        public string $status = 'scheduled',
        public ?string $preOpNotes = null,
        public ?string $scheduledAt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            bookingId: $data['booking_id'],
            dept: $data['dept'] ?? 'surgery',
            orBedId: isset($data['or_bed_id']) ? (int) $data['or_bed_id'] : null,
            bedNo: isset($data['bed_no']) ? (int) $data['bed_no'] : null,
            surgeonId: $data['surgeon_id'] ?? null,
            eye: $data['eye'] ?? null,
            procedure: $data['procedure'] ?? null,
            anaesthesia: $data['anaesthesia'] ?? null,
            status: $data['status'] ?? 'scheduled',
            preOpNotes: $data['pre_op_notes'] ?? null,
            scheduledAt: $data['scheduled_at'] ?? null,
        );
    }
}
