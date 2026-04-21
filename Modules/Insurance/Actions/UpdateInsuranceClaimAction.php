<?php

namespace Modules\Insurance\Actions;

use Modules\Admin\Services\ActivityLogService;
use Modules\Booking\Enums\PayStatus;
use Modules\Insurance\Models\InsuranceClaim;
use Modules\Insurance\States\PaidState;

class UpdateInsuranceClaimAction
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function execute(InsuranceClaim $claim, array $data): InsuranceClaim
    {
        $oldStatus = (string) $claim->status;

        $claim->update($data);
        $claim->refresh();

        if ($claim->status instanceof PaidState && $oldStatus !== PaidState::$name && $claim->booking_id) {
            $claim->booking->update(['pay_status' => PayStatus::Paid->value]);
        }

        $this->activityLogService->log(
            action: 'update',
            module: 'insurance_claims',
            recordId: $claim->id,
            description: "تحديث مطالبة التأمين: {$oldStatus} → {$data['status']}",
            newValues: $data,
        );

        return $claim;
    }
}
